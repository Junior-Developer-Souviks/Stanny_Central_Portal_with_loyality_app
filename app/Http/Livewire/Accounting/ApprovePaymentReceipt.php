<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\{Payment,User,PaymentCollection,Ledger,Journal};
use Illuminate\Support\Facades\DB;


class ApprovePaymentReceipt extends Component
{
      public $paymentId;
    public $payment;

    // Payment fields
    public $staff_id;
    public $customer_id;
    public $supplier_id;
    public $expense_id;
    public $payment_for;
    public $payment_in;
    public $bank_cash;
    public $voucher_no;
    public $payment_date;
    public $next_payment_date;
    public $payment_mode;
    public $amount;
    public $chq_utr_no;
    public $bank_name;
    public $transaction_no;
    public $withdrawal_charge;
    public $deposit_date;
    public $credit_date;
    public $cheque_file;
    public $receipt_copy_upload;
    public $narration;
    public $is_gst;
    public $is_ledger_added;
    public $is_approved;
    public $readonly = true;
    public $collection;
    // Helpers
    public $activePayementMode;
    public $staffs;
    
        public function mount($id)
        {
            
            $this->paymentId = $id;
              // 1️⃣ Get collection first
            $collection = PaymentCollection::with(['payment'])->findOrFail($id);
            $this->collection = $collection;
            $this->staffs = User::where('user_type', 0)->get(); // all staff
    
            // 2️⃣ Get main payment from collection
            $payment = Payment::with(['creator','customer','supplier','expense','collection'])
                ->findOrFail($this->collection->payment_id);
                
            $this->payment = $payment;
            // dd($payment->collection);
            // Fill all fields
            $this->fill([
                'staff_id' => $payment->creator->id ?? null,
                'customer_id' => $payment->customer_id,
                'supplier_id' => $payment->supplier_id,
                'expense_id' => $payment->expense_id,
                'payment_for' => $payment->payment_for,
                'payment_in' => $payment->payment_in,
                'bank_cash' => $payment->bank_cash,
                'voucher_no' => $payment->voucher_no,
                'payment_date' => $payment->payment_date,
                'next_payment_date' => $payment->collection->credit_date ?? null,
                'payment_mode' => strtolower($payment->payment_mode),
                'activePayementMode' => strtolower($payment->payment_mode),
                'amount' => $payment->amount,
                'chq_utr_no' => $payment->chq_utr_no ?? $payment->collection->cheque_number ?? null,
                'bank_name' => $payment->bank_name ?? $payment->collection->bank_name ?? null,
                'transaction_no' => $payment->collection->transaction_no ?? null,
                'withdrawal_charge' => $payment->collection->withdrawal_charge ?? null,
                'deposit_date' => $payment->collection->cheque_date ?? null,
                'credit_date' => $payment->collection->credit_date ?? null,
                'cheque_file' => $payment->collection->cheque_photo ?? null,
                'receipt_copy_upload' => $payment->collection->receipt_copy_upload ?? null,
                'narration' => $payment->narration,
                
            ]);
        }
        
        public function approvePayment(){
           
    
            DB::beginTransaction();
            try {
            $ledgerData = [
                 'user_type' => $this->payment->stuff_id ? 'staff'
                        : ($this->payment->customer_id ? 'customer'
                        : ($this->payment->admin_id ? 'partner'
                        : 'supplier')),
                        
                    'transaction_id' => $this->payment->voucher_no,
                    'transaction_amount' => $this->payment->amount,
                    'payment_id' => $this->payment->id,
                    'bank_cash' => $this->payment->bank_cash,
                     'is_credit'           => 1,
                     'is_debit'            => 0,
                    'entry_date' => $this->payment->payment_date,
                    'purpose'             => 'payment_receipt',
                    'purpose_description' => 'customer payment',
            ];
            
                if ($this->payment->stuff_id) $ledgerData['staff_id'] = $this->payment->stuff_id;
                if ($this->payment->customer_id) $ledgerData['customer_id'] = $this->payment->customer_id;
                if ($this->payment->admin_id) $ledgerData['admin_id'] = $this->payment->admin_id;
                if ($this->payment->supplier_id) $ledgerData['supplier_id'] = $this->payment->supplier_id;
                
             Ledger::create($ledgerData);
            
               $journalData = [
                    'transaction_amount' => $this->payment->amount,
                    'is_credit' => 1,
                    'entry_date' => $this->payment->payment_date,
                    'payment_id' => $this->payment->id,
                    'bank_cash' => $this->payment->bank_cash,
                    'purpose'             => 'payment_receipt',
                    'purpose_description' => 'customer payment',
                    'purpose_id' => $this->payment->voucher_no,
                ];
                
                Journal::create($journalData);
                
                 $this->payment->update([
                    'is_ledger_added' => 1,
                    'is_approved' => 1,
                    'approved_by' => auth()->guard('admin')->id()
                ]);
                
                 $this->collection->update([
                    'is_ledger_added' => 1,
                    'is_approve' => 1,
                ]);
                // dd($this->payment);
                DB::commit();
    
                return redirect()->route('admin.accounting.payment_collection');
    
                session()->flash('message', 'Expense approved successfully');
    
            } catch (\Exception $e) {
                dd($e->getMessage());
                DB::rollBack();
                session()->flash('error', $e->getMessage());
            }
        }

    
    public function render()
    {
        return view('livewire.accounting.approve-payment-receipt');
    }
}
