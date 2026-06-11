<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EditExpense extends Component
{
    public $expenseId;
    public $voucher_no, $amount, $payment_date, $payment_mode ;
    // public $activePaymentMode; 
    public $activePaymentMode = 'Cash';
    public $chq_utr_no, $bank_name, $narration, $expense_id, $user_type;
    // public $staff_id, $customer_id, $supplier_id, $activePaymentMode;

    public function mount($expenseId)
    {
        $expense = Payment::findOrFail($expenseId);
    
        $this->expenseId = $expense->id;
        $this->voucher_no = $expense->voucher_no;
        $this->amount = $expense->amount;
        $this->payment_date = $expense->payment_date;
        $this->payment_mode = $expense->payment_mode;
        $this->chq_utr_no = $expense->chq_utr_no;
        $this->bank_name = $expense->bank_name;
        $this->narration = $expense->narration;
        
        // Set active payment mode based on the retrieved data
        $this->activePaymentMode = $expense->payment_mode;
    }

    
    public function ChangePaymentMode($mode)
    {
        $this->activePaymentMode = $mode; // Update payment mode dynamically
    }

    public function updateExpense()
    {
        $this->validate([
            'amount' => 'required|numeric',
            'payment_date' => 'required',
            'payment_mode' => 'required',
           
        ], [
            'payment_date.required' => "Please add date of payment",
            'payment_mode.required' => "Please mention mode of payment",
            'amount.required' => "Please add amount",
            'amount.numeric' => "Amount must be a number",
        ]);

        

        DB::beginTransaction();

        try {
            // dd($this->all());
            $payment = Payment::findOrFail($this->expenseId);
            $payment->update([
                'voucher_no' => $this->voucher_no,
                'payment_date' => $this->payment_date,
                'payment_mode' => $this->payment_mode,
                'payment_in' => ($this->payment_mode != 'Cash') ? 'bank' : 'Cash',
                'bank_cash' => ($this->payment_mode == 'Cash') ? 'Cash' : 'bank',
                'amount' => $this->amount,
                'bank_name' => $this->bank_name,
                'chq_utr_no' => $this->chq_utr_no,
                'narration' => $this->narration,
                'updated_by' => Auth::guard('admin')->user()->id,
                'updated_at' => now()
            ]);
// dd()

            Ledger::where('payment_id', $this->expenseId)->update([
                'transaction_id' => $this->voucher_no,
                'transaction_amount' => $this->amount,
                'bank_cash' => ($this->payment_mode == 'Cash') ? 'Cash' : 'bank',
                'entry_date' => $this->payment_date,
                'updated_at' => now()
            ]);

            Journal::where('payment_id', $this->expenseId)->update([
                'transaction_amount' => $this->amount,
                'entry_date' => $this->payment_date,
                'bank_cash' => ($this->payment_mode == 'Cash') ? 'Cash' : 'bank',
                'updated_at' => now()
            ]);

            DB::commit();

            Session::flash('message', "Expense updated successfully for " . $this->user_type);
            return redirect()->route('admin.accounting.cashbook_module');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Something went wrong: ' . $e->getMessage());
            return redirect()->route('admin.accounting.list.depot_expense');
        }
    }

    public function render()
    {
        return view('livewire.accounting.edit-expense');
    }
}
