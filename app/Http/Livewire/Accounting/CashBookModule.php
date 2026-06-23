<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\PaymentCollection;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\PaymentRevoke;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\WithPagination;
use App\Models\{DayCashEntry,Branch};
use Illuminate\Support\Facades\Log;


class CashBookModule extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $totalCollections = 0;
    public $totalExpenses = 0;
    public $totalWallet = 0;
    public $paymentCollections = [];
    public $paymentExpenses = [];
    public $totalcashCollections = 0;
    public $totalneftCollections = 0;
    public $totalchequeCollections = 0;
    public $totaldigitalCollections = 0;

    public $start_date;
    public $end_date;
    public $searchStaff = '';
    public $selectedStaffId = null;
    public $staffSuggestions = [];
    public $branches = [];
    public $staff_branch = '';

    protected $listeners = ['revoke-payment-confirmed' => 'revokePayment'];

    public function mount()
    {
        $this->branches = Branch::latest()->get();
        // Default to current month
        $this->start_date = Carbon::now()->toDateString();
        $this->end_date = Carbon::now()->toDateString();
         $this->staff_branch = '';
    }

    public function AddStartDate($date){
        $this->start_date = $date;
    }

    public function AddEndDate($date){
        $this->end_date = $date;
    }

    public function resetForm(){
        $this->reset([
            'start_date',
            'end_date',
        ]);
    }

    public function revokePayment($id)
    {
         $payment_collections = PaymentCollection::find($id);

        if (!$payment_collections) {
            session()->flash('error', 'Payment not found.');
            return;
        }

        $customer_id = $payment_collections->customer_id;
        $voucher_no = $payment_collections->voucher_no;
        $collection_amount = $payment_collections->collection_amount;
        $payment_id = $payment_collections->payment_id;

        $paymentRevoke = array(
            'customer_id' => $customer_id,
            'done_by'     => Auth::guard('admin')->user()->id,
            'voucher_no'  => $voucher_no,
            'collection_amount' => $collection_amount,
            'paymentcollection_data_json' => json_encode($payment_collections),
            'created_at' => now()
        );

        PaymentRevoke::insert($paymentRevoke);

        # Reset invoices
        $invoiceIds = Invoice::where('customer_id', $customer_id)->pluck('id')->toArray();
        Invoice::whereIn('id', $invoiceIds)->update([
            'required_payment_amount' => \DB::raw('net_price'),
            'payment_status' => 0,
            'is_paid' => 0
        ]);

        # Delete invoice payments
        InvoicePayment::whereIn('invoice_id', $invoiceIds)->delete();

        # Delete ledger, journal, and payment
        Ledger::where('payment_id', $payment_id)->delete();
        Journal::where('payment_id', $payment_id)->delete();
        Payment::where('id', $payment_id)->delete();

        # Finally delete collection itself
        PaymentCollection::where('id', $id)->delete();

        session()->flash('success', 'Payment revoked successfully.');
    }

    private function resetInvoicePayments($customer_id, $collection_data){
        foreach($collection_data as $payments){
            $payment_amount = $payments['collection_amount'];
            $payment_collection_id = $payments['id'];
            $check_invoice_payments = InvoicePayment::where('voucher_no','=',$payments['voucher_no'])->get()->toarray();
            if(empty($check_invoice_payments)){
                $amount_after_settlement = $payment_amount;
                 $invoice = Invoice::where('customer_id',$customer_id)->where('is_paid',0)->orderBy('id','asc')->get();
                 $sum_inv_amount = 0;
                 foreach ($invoice as $inv) {
                    $amount = $inv->required_payment_amount;
                    $sum_inv_amount += $amount;
                    if($amount == $payment_amount){
                        // die('Full Covered');
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>0,
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);
                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $payments['voucher_no'],
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => 0,
                            'created_at' => $payments['created_at'],
                            'updated_at' => $payments['created_at']
                        ]);
                        $amount_after_settlement = 0;
                    }else{
                        // die('Not Full Covered');
                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>0,
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $payments['voucher_no'],
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount,
                                'rest_amount' => 0,
                                'created_at' => $payments['created_at'],
                                'updated_at' => $payments['created_at']
                            ]);
                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                            $rest_payment_amount = ($amount - $amount_after_settlement);
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>$rest_payment_amount,
                                'payment_status' => 1,
                                'is_paid'=>0
                            ]);
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $payments['voucher_no'],
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount_after_settlement,
                                'rest_amount' => $rest_payment_amount,
                                'created_at' => $payments['created_at'],
                                'updated_at' => $payments['created_at']
                            ]);
                            $amount_after_settlement = 0;
                        }else{

                        }
                    }
                 }
            }
        }
    }

    public function downloadInvoice($payment_id)
    {
        $invoice_payments = [];
        $data = PaymentCollection::with(['customer', 'user'])
                    ->where('id', $payment_id)
                    ->firstOrFail();
        if($data){
            $invoice_payments = InvoicePayment::with('invoice')->where('voucher_no','=',$data->voucher_no)->get();
        }
        // Generate PDF
        $pdf = PDF::loadView('invoice.pdf', compact('data','invoice_payments'));

        // Download the PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        },  $data->voucher_no . '.pdf');
    }

    // public function SearchStaff($value){
    //     if(strlen($value) > 1){
    //         $this->staffSuggestions = User::where('user_type', 0)
    //             ->where('name', 'like', '%' . $value . '%')
    //             ->limit(10)
    //             ->get();
    //     }else{
    //         $this->staffSuggestions = [];
    //     }
    // }

    // public function selectStaff($staffId,$name){
    //     $this->selectedStaffId = $staffId;
    //     $this->searchStaff = $name;
    //     $this->staffSuggestions = [];
    // }
    
    public function SearchStaff($value){
        $user = Auth::guard('admin')->user();
        $isAuthorized = $user->is_super_admin || $user->designation == 14;  // ✅ Get user authorization
    
        if(strlen($value) > 1){
            $query = User::where('user_type', 0)
                ->where('name', 'like', '%' . $value . '%')
                ->limit(10);
    
            // ✅ FIX: Restrict search results based on authorization
            if (!$isAuthorized) {
                $query->where('id', $user->id);  // Non-admin can only find themselves
            }
    
            $this->staffSuggestions = $query->get();
        }else{
            $this->staffSuggestions = [];
        }
    }
    
   public function selectStaff($staffId,$name){
        $user = Auth::guard('admin')->user();
        $isAuthorized = $user->is_super_admin || $user->designation == 14;
    
        if (!$isAuthorized && $staffId != $user->id) {
            session()->flash('error', 'You can only view your own data.');
            return;
        }
    
        $this->selectedStaffId = $staffId;
        $this->searchStaff = $name;
        $this->staffSuggestions = [];
    }

    public function selectBranch()
    {
        $this->selectedStaffId = null;
        $this->searchStaff = '';
        $this->staffSuggestions = [];
    }

    
    public function render()
    {
        $user = Auth::guard('admin')->user();
        $isAuthorized = $user->is_super_admin || $user->designation == 14;
    
        $startDate = Carbon::parse($this->start_date)->startOfDay();
        $endDate = Carbon::parse($this->end_date)->endOfDay();
    
        // Get earliest transaction date
        $firstCollectionDate = PaymentCollection::where('is_approve', 1)
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at')
            ->value('created_at');
    
        // ====================== OPENING BALANCE ======================
        // ==========================
        // OPENING COLLECTION
        // ==========================
        $pastCollections = PaymentCollection::where('is_approve', 1)
            ->whereDate('cheque_date', '<', $this->start_date)
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->sum('collection_amount');
        
        // ==========================
        // OPENING EXPENSE
        // ==========================
        $pastExpenses = Journal::where('is_debit', 1)
            ->whereNotNull('payment_id')
            ->whereDate('entry_date', '<', $this->start_date)
            ->when(!$isAuthorized, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->where('stuff_id', $user->id);
                });
            })
            ->when($this->selectedStaffId, function ($query) {
                $query->whereHas('payment', function ($q) {
                    $q->where('stuff_id', $this->selectedStaffId);
                });
            })
            ->when($this->staff_branch, function ($query) {
                $query->whereHas('payment.staff', function ($q) {
                    $q->where('branch_id', $this->staff_branch);
                });
            })
            ->sum('transaction_amount');
        
        $openingBalance = $pastCollections - $pastExpenses;
        
        // ==========================
        // CURRENT COLLECTIONS
        // ==========================
        $this->totalCollections = PaymentCollection::where('is_approve', 1)
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->sum(\DB::raw('COALESCE(collection_amount, 0) + COALESCE(withdrawal_charge, 0)'));
        
        // ==========================
        // PAYMENT TYPE BREAKDOWN - FIX: Add these calculations
        // ==========================
        
        // Total Cash Collections
        $this->totalcashCollections = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'cash')
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->sum('collection_amount');
        
        // Total NEFT Collections
        $this->totalneftCollections = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'neft')
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->sum('collection_amount');
        
        // Total Cheque Collections
        $this->totalchequeCollections = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'cheque')
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->sum('collection_amount');
        
        // Total Digital Payment Collections
        $this->totaldigitalCollections = PaymentCollection::where('is_approve', 1)
            ->where('payment_type', 'digital_payment')
            ->when(!$isAuthorized, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->whereBetween('cheque_date', [$startDate, $endDate])
            ->sum(\DB::raw('COALESCE(collection_amount, 0) + COALESCE(withdrawal_charge, 0)'));
        
        // ==========================
        // CURRENT EXPENSES
        // ==========================
        $this->totalExpenses = Journal::where('is_debit', 1)
            ->whereNotNull('payment_id')
            ->when(!$isAuthorized, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->where('stuff_id', $user->id);
                });
            })
            ->when($this->selectedStaffId, function ($query) {
                $query->whereHas('payment', function ($q) {
                    $q->where('stuff_id', $this->selectedStaffId);
                });
            })
            ->when($this->staff_branch, function ($query) {
                $query->whereHas('payment.staff', function ($q) {
                    $q->where('branch_id', $this->staff_branch);
                });
            })
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('transaction_amount');
        
        // ==========================
        // CASH COLLECTED FROM STAFF
        // ==========================
        $collectedFromStaff = DayCashEntry::where('type', 'collected')
            ->whereDate('payment_date', '>=', $firstCollectionDate ?? $this->start_date)
            ->whereDate('payment_date', '<=', $this->end_date)
            ->when(!$isAuthorized, fn($q) => $q->where('staff_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('staff_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('staff', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->sum('amount');
        
        // ==========================
        // CASH GIVEN TO STAFF
        // ==========================
        $givenToStaff = DayCashEntry::where('type', 'given')
            ->whereDate('payment_date', '>=', $firstCollectionDate ?? $this->start_date)
            ->whereDate('payment_date', '<=', $this->end_date)
            ->when(!$isAuthorized, fn($q) => $q->where('staff_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('staff_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) =>
                $q->whereHas('staff', fn($u) =>
                    $u->where('branch_id', $this->staff_branch)
                )
            )
            ->sum('amount');
        
        // ==========================
        // FINAL WALLET
        // ==========================
        $this->totalWallet =
            $openingBalance
            + $this->totalCollections
            - $this->totalExpenses
            - $collectedFromStaff
            + $givenToStaff;
        
        // Payment Collections Table
        $paymentQuery = PaymentCollection::where('is_approve', 1)
            ->when(!$isAuthorized && !$this->selectedStaffId, fn($q) => $q->where('user_id', $user->id))
            ->when($this->selectedStaffId, fn($q) => $q->where('user_id', $this->selectedStaffId))
            ->when($this->staff_branch, fn($q) => $q->whereHas('user', fn($u) => $u->where('branch_id', $this->staff_branch)))
            ->where(function ($query) {
                $query->where('payment_type', '!=', 'cheque')
                      ->orWhere('payment_type', 'cheque');
            });
        
        if ($this->start_date && $this->end_date) {
            $paymentQuery->whereBetween('cheque_date', [$startDate, $endDate]);
        }
        
        $this->paymentCollections = $paymentQuery->orderByDesc('created_at')
            ->where('collection_amount', '>', 0)
            ->get();
        
        // Expenses Table
        $validPaymentIds = Journal::whereNotNull('payment_id')->pluck('payment_id');
        
        $paymentExpenseQuery = Payment::where('payment_for', 'debit')
            ->whereIn('id', $validPaymentIds)
            ->when($this->selectedStaffId, fn($q) => $q->where('stuff_id', $this->selectedStaffId))
            ->when(!$this->selectedStaffId && !$isAuthorized, fn($q) => $q->where('stuff_id', $user->id));
        
        if ($this->start_date && $this->end_date) {
            $paymentExpenseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $this->paymentExpenses = $paymentExpenseQuery->orderByDesc('created_at')->get();
        
        return view('livewire.accounting.cash-book-module');
    }
   

    




}
