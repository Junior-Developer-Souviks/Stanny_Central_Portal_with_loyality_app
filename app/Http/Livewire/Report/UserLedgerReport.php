<?php

namespace App\Http\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LedgerExport; 
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class UserLedgerReport extends Component
{
    use WithPagination;
    public $selected_customer;
    public $staff_id;
    public $user_type;
    public $active_details;
    public $searchResults = [];
    public $customer_id;

    public $supplier_id;
    public $from_date,$to_date,$bank_cash;

    public $staffSearchTerm = '';
    public $staffSearchResults = [];

    public $customerSearchTerm = '';
    public $customerSearchResults = [];

    public $supplierSearchTerm = '';
    public $supplierSearchResults = [];
    public $search;
    public $errorMessage = [];
    public $ledgerData = [];

    public $day_opening_amount = 0;
    public $is_opening_bal = 0;
    public $is_opening_bal_showable = 1;
    public $opening_bal_date = "";
    public $non_tr_day_opening_amount = 0;
    public $isTransactionFound = false;

    public function mount(){
        $this->from_date = date('Y-m-01'); // First day of the current month
        $this->to_date = date('Y-m-d'); 
    }
    public function ResetOpeningBalanceField(){
        $this->reset(['day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','non_tr_day_opening_amount','isTransactionFound']);
    }
    public function FindCustomer($searchTerm)
    {
        $this->searchResults = User::where('name', 'like', '%' . $searchTerm . '%')
            ->orWhere('phone', 'like', '%' . $searchTerm . '%')
            ->take(10)
            ->get();
    }
    
    public function getUser($value){
        $this->user_type = $value;
        $this->reset(['customerSearchTerm','supplierSearchTerm','staffSearchTerm']);
    }
    public function PaymentMode($value){
        $this->bank_cash = $value;
    }
    public function updateFromDate($value){
        $this->from_date = $value;
    }
    public function updateToDate($value){
        $this->to_date = $value;
    }
    
    public function selectCustomer($customerId)
    {
        $this->selected_customer = $customerId;
        $this->searchResults = [];
    }

    public function resetForm()
    {
        $this->reset(['user_type','selected_customer','staff_id','customer_id','supplier_id']);
    }
    
    public function customerDetails($id)
    {
        $this->active_details = $id;
    }
    public function searchStaff()
    {
        if (!empty($this->staffSearchTerm)) {

            $this->staffSearchResults = User::where('user_type', 0) // 0 for staff
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->staffSearchTerm . '%');
                })
                ->get();
        } else {
            $this->staffSearchResults = [];
        }
    }

    public function selectStaff($staffId)
    {
        $staff = User::find($staffId);
        if ($staff) {
            $this->staff_id = $staff->id;
            $this->staffSearchTerm = $staff->name; // Show selected staff name
            $this->reset(['customer_id','supplier_id']);
        }
        $this->staffSearchResults = []; // Hide dropdown after selection
        $this->reset(['errorMessage']);
    }
    public function searchCustomer()
    {
        if (!empty($this->customerSearchTerm)) {
            $this->customerSearchResults = User::where('user_type', 1) // 1 for customers
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->customerSearchTerm . '%');
                })
                ->get();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function selectCustomers($customerId)
    {
        $customer = User::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerSearchTerm = $customer->name; // Display selected name
            $this->reset(['staff_id','supplier_id']);
        }
        $this->customerSearchResults = []; // Hide dropdown
        $this->reset(['errorMessage']);
    }

    public function searchSupplier()
    {
        if (!empty($this->supplierSearchTerm)) {
            $this->supplierSearchResults = Supplier::where('name', 'like', '%' . $this->supplierSearchTerm . '%')
                ->get();
        } else {
            $this->supplierSearchResults = [];
        }
    }

    public function selectSupplier($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        if ($supplier) {
            $this->supplier_id = $supplier->id;
            $this->supplierSearchTerm = $supplier->name; // Display selected name
            $this->reset(['customer_id','staff_id']);
            
        }
        $this->reset(['errorMessage']);
        $this->supplierSearchResults = []; // Hide dropdown
    }
   
    public function generatePDF()
    {
        $selectUserName = ''; // Default value
        if ($this->user_type === 'staff' && $this->staff_id) {
            $staff = User::find($this->staff_id);
            $selectUserName = $staff ? $staff->name : 'Unknown Staff';
        } elseif ($this->user_type === 'customer' && $this->customer_id) {
            $customer = User::find($this->customer_id);
            $selectUserName = $customer ? $customer->name : 'Unknown Customer';
        } elseif ($this->user_type === 'supplier' && $this->supplier_id) {
            $supplier = Supplier::find($this->supplier_id);
            $selectUserName = $supplier ? $supplier->name : 'Unknown Supplier';
        }
        $net_value = $cred_value = $deb_value = 0;
        $cred_ob_amount = $deb_ob_amount = "";
        $data = [];
    
        // Calculate opening balance
        $getCrDrOB = Helper::getCrDr($this->day_opening_amount);
        if ($getCrDrOB == 'Cr') {
            $cred_ob_amount = $this->day_opening_amount;
            $cred_value += $cred_ob_amount;
        } elseif ($getCrDrOB == 'Dr') {
            $deb_ob_amount = Helper::replaceMinusSign($this->day_opening_amount);
            $deb_value += $deb_ob_amount;
        }
    
        if (!empty($this->is_opening_bal_showable)) {
            $net_value += $this->day_opening_amount;
        }
    
        // Add Opening Balance Row if required
        if (!empty($this->ledgerData) && $this->is_opening_bal_showable == 1) {
            $data[] = [
                'Date' => date('d-m-Y', strtotime($this->from_date)),
                'purpose' =>"Opening Balance",
                'purpose_desc' => ucfirst($this->user_type).' Opening Balance',
                'debit' => Helper::replaceMinusSign($deb_ob_amount),
                'credit' => $cred_ob_amount,
                'closing' => Helper::replaceMinusSign(number_format($this->day_opening_amount)) .' '. Helper::getCrDr($this->day_opening_amount),
            ];
        }
    
        // Process transactions
        foreach ($this->ledgerData as $item) {
            $debit_amount = $credit_amount = '';
    
            if (!empty($item->is_credit)) {
                $credit_amount = number_format((float) $item->transaction_amount);
                $net_value += $item->transaction_amount;
                $cred_value += $item->transaction_amount;
            }
    
            if (!empty($item->is_debit)) {
                $debit_amount = number_format((float) $item->transaction_amount);
                $net_value -= $item->transaction_amount;
                $deb_value += $item->transaction_amount;
            }
    
            $data[] = [
                'Date' => date('d-m-Y', strtotime($item->created_at)),
                'purpose' => ucwords(str_replace('_', ' ', $item->purpose)) . '(' . ucwords($item->bank_cash).')',
                'purpose_desc' =>$item->purpose_description,
                'debit' => $debit_amount,
                'credit' => $credit_amount,
                'closing' => Helper::replaceMinusSign(number_format($net_value)) .' '. Helper::getCrDr($net_value),
            ];
        }
        $report = [
            'user_type' => $this->user_type,
            'select_user_name' => $selectUserName,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'ledgers' => $data,
        ];

        $pdf = Pdf::loadView('ledger.pdf', $report)->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'ledger_report.pdf');
    }

    public function LedgerUserData() {
        $this->ResetOpeningBalanceField();
        if (!empty($this->user_type)) {
            $opening_bal = Ledger::query();
            $query = Ledger::query();
            // Filter by date range
            if (!empty($this->from_date)) {
                $query->whereDate('entry_date', '>=', $this->from_date);
            }
            if (!empty($this->to_date)) {
                $query->whereDate('entry_date', '<=', $this->to_date);
            }
    
            // Filter based on user type
            if ($this->user_type === 'staff') {
                if (!empty($this->staff_id)) {
                    $query->where('staff_id', $this->staff_id);
                    $opening_bal->where('staff_id', $this->staff_id);
                } else {
                    $this->ledgerData = collect(); // Empty collection
                    $this->errorMessage['staff'] = 'Please type staff name';
                    return;
                }
            } elseif ($this->user_type === 'customer') {
                if (!empty($this->customer_id)) {
                    $query->where('customer_id', $this->customer_id);
                    $opening_bal->where('customer_id', $this->customer_id);
                } else {
                    $this->ledgerData = collect();
                    $this->errorMessage['customer'] = 'Please type customer name';
                    return;
                }
            } elseif ($this->user_type === 'supplier') {
                if (!empty($this->supplier_id)) {
                    $query->where('supplier_id', $this->supplier_id);
                    $opening_bal->where('supplier_id', $this->supplier_id);
                } else {
                    $this->ledgerData = collect();
                    $this->errorMessage['supplier'] = 'Please type supplier name';
                    return;
                }
            }

            $check_ob_exist_customer = Ledger::where('purpose','opening_balance')->where('user_type', 'customer')->where('customer_id',$this->customer_id)->orderBy('id','asc')->first();

            if(!empty($check_ob_exist_customer)){
                $from_date = ($this->from_date < $check_ob_exist_customer->entry_date) ? $check_ob_exist_customer->entry_date : $this->from_date;
                // dd($check_ob_exist_customer,$from_date);
                $this->is_opening_bal = 1;
                $this->opening_bal_date = $check_ob_exist_customer->entry_date;
                // $this->from_date = $check_ob_exist_customer->entry_date;
                if($from_date == $check_ob_exist_customer->entry_date){                    
                    $this->is_opening_bal_showable = 0;  
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_customer->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                  
                }                
                
            } else {
                $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($this->from_date)))."'  ");
            } 

            /* +++++++++++++++++++ */
            $opening_bal = $opening_bal->orderBy('entry_date','ASC');  
            $opening_bal = $opening_bal->orderBy('updated_at','ASC');  
            $opening_bal = $opening_bal->get();
            $day_opening_amount = 0;
            foreach($opening_bal as $ob){
                if(!empty($ob->is_credit)){
                    $day_opening_amount += $ob->transaction_amount;
                }
                if(!empty($ob->is_debit)){
                    $day_opening_amount -= $ob->transaction_amount;
                }
            }
            $this->day_opening_amount = $day_opening_amount;

            // Filter by bank or cash type
            if (!empty($this->bank_cash)) {
                $query->where('bank_cash', $this->bank_cash);
            }
    
            // Fetch and store data
            $this->reset(['errorMessage']);
            $this->ledgerData = $query->orderBy('entry_date','ASC')->get();
        }else{
            $this->reset('ledgerData');
        }
    }
    
    public function render() {
        $this->LedgerUserData(); // Call data fetching function
    
        return view('livewire.report.user-ledger-report', [
            'ledgerData' => $this->ledgerData // Use the property
        ]);
    }
    
    public function exportLedger()
    {
        // Call the LedgerExport with dynamic filters
        return Excel::download(
            new LedgerExport(
                $this->ledgerData,
                $this->day_opening_amount,
                $this->is_opening_bal_showable,
                $this->from_date,
                $this->to_date,
            ),
            'ledger.csv'
        );
    }
    
}

