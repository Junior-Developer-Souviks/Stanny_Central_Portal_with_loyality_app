<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\PaymentCollection;
use App\Models\PaymentRevoke;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Ledger;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentCollectionIndex extends Component
{
    use WithPagination;
    public $searchResults = [];
    public $data = [];
    public $total;
    public $staff_id;
    public $staffs = [];
    public $selected_customer;
    public $selected_customer_id;
    public $active_details = 0;
    public $auth;
    protected $listeners = ['revoke-payment-confirmed' => 'revokePayment'];
    protected $paginationTheme = 'bootstrap';
    public $canApprove = false;
    
     public function updatingSearch()
    {
        $this->resetPage();
    }
    
     public function mount(){
        $this->auth = Auth::guard('admin')->user();
        $isAuthorizedViewer = $this->auth->is_super_admin || ($this->auth->designation == 14);
          // Check approve permission via Designation → designation_permissions → permissions
        $this->canApprove = $this->auth->is_super_admin || Designation::where('id', $this->auth->designation)
            ->whereHas('permissions', function($query) {
                $query->where('route', 'admin.accounting.payment_receipt_detail');
            })
            ->exists();
        $this->staffs = User::where('user_type', 0)
        // ->when(!$isAuthorizedViewer, fn($query) => $query->where('id', $this->auth->id))// Auth-wise filtering
          ->when(!$isAuthorizedViewer, function ($query) {
            return $query->where('id', $this->auth->id);
        })
        ->select('name', 'id')
        ->orderBy('name', 'ASC')
        ->get();
    }

    public function CollectionData(){
         // Get the authenticated user
        $paginate = 20;
        $customer_id = $this->selected_customer_id;
        $staff_id = $this->staff_id;
        // Determine if user can see all data
        $isAuthorizedViewer = $this->auth->is_super_admin || ($this->auth->designation == 14);

        // Query with conditions
         $query = PaymentCollection::with(['customer', 'user'])
            ->when(!empty($customer_id), function ($query) use ($customer_id) {
                return $query->where('customer_id', $customer_id);
            })
            ->when(!empty($staff_id), function ($query) use ($staff_id) {
                return $query->where('user_id', $staff_id);
            })
            ->when(!$isAuthorizedViewer, function ($query) {
                return $query->where('user_id', $this->auth->id);
            })
            ->where('collection_amount', '>', 0)
            ->orderBy('cheque_date', 'desc');

        // Set total count
        $this->total = $query->count();
        return $query->paginate($paginate);
    }
    
    public function resetForm(){
        $this->active_details = 0;
        $this->reset(['selected_customer','selected_customer_id', 'staff_id']);
    }
    public function FindCustomer($term){
        $this->searchResults = Helper::GetCustomerDetails($term);
    }

    public function selectCustomer($customer_id){
        $customer = User::find($customer_id);
        if($customer){
            $this->selected_customer = $customer->name.'('.$customer->phone.')';
            $this->selected_customer_id = $customer->id;
        }else{
            $this->reset(['selected_customer','selected_customer_id']);
        }
        $this->searchResults = [];
        $this->resetPage();
    }

    public function CollectedBy($value){
        $this->staff_id = $value;
        $this->resetPage();
    }

   public function customerDetails($id)
    {
        $this->active_details = ($this->active_details == $id) ? '' : $id;
    }

    public function revokePayment($id){
        $payment_collections = PaymentCollection::find($id);
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
            'created_at' => date('Y-m-d H:i:s')
        );

        PaymentRevoke::insert($paymentRevoke);
        $collection_data = $paymentIds = array();
        $other_payment_collections = PaymentCollection::where('customer_id',$customer_id)->where('id','!=',$id)->orderBy('cheque_date','asc')->get();
        foreach($other_payment_collections as $collections){
            $paymentIds[] = $collections->payment_id;
            $collection_data[] = array(
                'id' => $collections->id,
                'customer_id' => $collections->customer_id,
                'user_id' => $collections->user_id,
                'admin_id' => $collections->admin_id,
                'payment_id' => $collections->payment_id,
                'collection_amount' => $collections->collection_amount,
                'cheque_date' => $collections->cheque_date,
                'voucher_no' => $collections->voucher_no,
                'payment_type' => $collections->payment_type,
                'created_at' => date('Y-m-d H:i:s', strtotime($collections->created_at))
            );
        }

        $invoiceIds = array();
        $all_invoices = Invoice::where('customer_id',$customer_id)->get();
        foreach($all_invoices as $invoice){
            $invoiceIds[] = $invoice->id;
            # Revert Invoice Required Amount to Net Amount and All Payment Status
            Invoice::where('id',$invoice->id)->update([
                'required_payment_amount' => $invoice->net_price,
                'payment_status' => 0,
                'is_paid' => 0
            ]);
        }
        # Delete Invoice Payments
        InvoicePayment::whereIn('invoice_id',$invoiceIds)->delete();
        # Delete Ledger
        Ledger::where('payment_id',$payment_id)->delete();
        # Delete Journal
        Journal::where('payment_id',$payment_id)->delete();
        # Delete Payment
        Payment::where('id',$payment_id)->delete();
        $this->resetInvoicePayments($customer_id,$collection_data);

        # Delete Payment Collection
        PaymentCollection::where('id',$id)->delete();
        session()->flash('success', 'Payment revoked successfully');
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
        $data = PaymentCollection::with(['customer.billingAddressLatest', 'user'])
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
    public function render()
    {
        $paginatedData = $this->CollectionData();
        return view('livewire.accounting.payment-collection-index', [
            'paymentData' => $paginatedData,
        ]);
    }
}
