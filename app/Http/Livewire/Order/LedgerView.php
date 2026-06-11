<?php

namespace App\Http\Livewire\Order;

use App\Models\Ledger;
use App\Models\Order;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB; // Add this line


class LedgerView extends Component
{
    public $orderId;
    public $id;
    public $order;
    public $transactions = [];
    public $transaction_date, $transaction_type, $paid_amount, $remarks,$payment_method;

    public $totalPaid = 0;
    public $totalRemaining = 0;
    public $allowPayment = false;

    public function mount($id)
    {
        // dd($this->orderId);
        // $this->orderId = $id;
        $this->order = Order::findOrFail($id);
        // dd($this->order);
        $this->loadTransactions();
    }

    public function togglePayment(){
        $this->allowPayment = !$this->allowPayment;
    }

    // Load all transactions for the given order
    public function loadTransactions()
    {
        $this->transactions = Ledger::where('order_id', $this->order->id)
            ->orderBy('transaction_date', 'DESC')
            ->get();

        $this->calculateSummary();
    }

    // Calculate total paid and remaining amounts from the ledger
    public function calculateSummary()
    {
        $this->totalPaid = $this->transactions->sum('paid_amount');
        $this->totalRemaining = $this->order->total_amount - $this->totalPaid;
    }

    // Add a new transaction to the ledger
    public function addPayment()
    {
        $this->validate([
            // 'transaction_date' => 'required|date',
            // 'transaction_type' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            // 'remarks' => 'nullable|string',
        ]);

        // Ensure paid amount does not exceed remaining balance
        if ($this->paid_amount > $this->totalRemaining) {
            session()->flash('error', 'Paid amount cannot exceed the remaining balance.');
            return;
        }

        // Create the ledger entry
        // Ledger::create([
        //     'user_id' => $this->order->customer_id,
        //     'order_id' =>$this->order->id,
        //     'transaction_date' => now()->format('Y-m-d'),
        //     'transaction_type' => "Debit",
        //     'payment_method' => $this->payment_method,
        //     'paid_amount' => $this->paid_amount,
        //     'remarks' => $this->remarks,
        // ]);
        // // $this->order->increment('paid_amount', $this->paid_amount);
        // $this->order->paid_amount = $this->paid_amount;
        // $this->order->remaining_amount = $this->order->total_amount - $this->order->paid_amount;
        // $this->order->last_payment_date = now();
        // $this->order->save();

        DB::transaction(function () {
            Ledger::create([
                'user_id' => $this->order->customer_id,
                'order_id' => $this->order->id,
                'transaction_date' => now()->format('Y-m-d'),
                'transaction_type' => "Debit",
                'purpose'  => 'Installment Payments',
                'purpose_description'  => 'Partial Payment for Order #'.$this->order->order_number,
                'payment_method' => $this->payment_method,
                'paid_amount' => $this->paid_amount,
                'remarks' => $this->remarks,
            ]);
        
            $this->order->increment('paid_amount', $this->paid_amount);
            $this->order->remaining_amount = max(0, $this->order->total_amount - $this->order->paid_amount);
            $this->order->last_payment_date = now()->format('Y-m-d');
            $this->order->payment_mode = $this->payment_method;
            $this->order->save();
        });
        
        // Reset input fields and reload transactions
        $this->resetInput();
        $this->loadTransactions();

        session()->flash('message', 'Transaction added successfully.');
    }

    // Reset form fields
    public function resetInput()
    {
        $this->transaction_date = null;
        $this->transaction_type = null;
        $this->paid_amount = null;
        $this->remarks = null;
    }

    public function render()
    {
        return view('livewire.order.ledger-view', [
            'order' => $this->order,
            'transactions' => $this->transactions,
        ]);
    }
}
