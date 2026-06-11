<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Journal;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;


class ApproveExpense extends Component
{
    public $payment;
    public $payment_id;
    
    public function mount($payment_id){
        $this->payment_id = $payment_id;
        $this->payment = Payment::with(
                'staff',
                'customer',
                'supplier',
                'expense',
                'creator',
                'partner'
            )->findOrFail($payment_id);
    }
    
        public function approveExpense()
        {
            if ($this->payment->is_ledger_added == 1) {
                session()->flash('message', 'Expense already approved');
                return;
            }
    
            DB::beginTransaction();
    
            try {
                $expense_name = $this->payment->expense->title ?? '';
                
                $ledgerData = [
                    'user_type' => $this->payment->stuff_id ? 'staff'
                        : ($this->payment->customer_id ? 'customer'
                        : ($this->payment->admin_id ? 'partner'
                        : 'supplier')),
    
                    'transaction_id' => $this->payment->voucher_no,
                    'transaction_amount' => $this->payment->amount,
                    'payment_id' => $this->payment->id,
                    'bank_cash' => $this->payment->bank_cash,
                    'is_debit' => 1,
                    'entry_date' => $this->payment->payment_date,
                    'purpose' => 'staff_expense',
                    'purpose_description' => 'Expense for staff - '.$expense_name,
                ];
    
                if ($this->payment->stuff_id) $ledgerData['staff_id'] = $this->payment->stuff_id;
                if ($this->payment->customer_id) $ledgerData['customer_id'] = $this->payment->customer_id;
                if ($this->payment->admin_id) $ledgerData['admin_id'] = $this->payment->admin_id;
                if ($this->payment->supplier_id) $ledgerData['supplier_id'] = $this->payment->supplier_id;
                
                Ledger::create($ledgerData);
    
                $journalData = [
                    'transaction_amount' => $this->payment->amount,
                    'is_debit' => 1,
                    'entry_date' => $this->payment->payment_date,
                    'payment_id' => $this->payment->id,
                    'bank_cash' => $this->payment->bank_cash,
                    'purpose' => 'staff_expense',
                    'purpose_description' => $expense_name,
                    'purpose_id' => $this->payment->voucher_no,
                ];
                
                
                Journal::create($journalData);
    
                $this->payment->update([
                    'is_ledger_added' => 1,
                    'is_approved' => 1,
                    'approved_by' => auth()->guard('admin')->id()
                ]);
                // dd($this->payment);
                DB::commit();
    
                return redirect()->route('admin.accounting.list.depot_expense');
    
                session()->flash('message', 'Expense approved successfully');
    
            } catch (\Exception $e) {
    
                DB::rollBack();
                session()->flash('error', $e->getMessage());
            }
        }
    
        
    
        public function render()
        {
            return view('livewire.accounting.approve-expense');
        }
}
