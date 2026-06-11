<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\PaymentCollection;
use App\Models\DayCashEntry as DayCashEntryModel;
use App\Models\Payment;
use App\Models\Journal;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DayCashEntry extends Component
{
    public $totalCollections = 0;
    public $totalCash = 0;
    public $totalNEFT = 0;
    public $totalCheque = 0;
    public $totalDigital = 0;
    public $totalWallet = 0;

    public $staff_id;
    public $entry_type;
    public $payment_cash = false;
    public $payment_digital = false;
    public $cashCollectedAmount;
    public $digitalCollectedAmount;
    public $collectedAmount;    
    public $staffs = [];

    public function mount()
    {
        $this->staffs = User::where('user_type', 0)
            ->whereIn('designation', [2,12,4])
            ->select('name', 'id','designation')
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function setEntryType($value)
    {
        $this->entry_type=$value;
    }

     public function toggleCashCheckbox()
    {
        $this->payment_cash = !$this->payment_cash;
    }

    public function toggleDigitalCheckbox()
    {
        $this->payment_digital = !$this->payment_digital;
    }

    public function fetchBalance($value)
    {
        $this->staff_id = $value;
        // $collections = PaymentCollection::where('user_id', $value)
        //     ->where('is_approve', 1)
        //     ->where('is_settled', 0)
        //     ->get();
         $query = PaymentCollection::where('user_id', $value)
        ->where('is_approve', 1);

        if ($this->entry_type === 'collected') {
            $query->where('is_settled', 0);
        } elseif ($this->entry_type === 'given') {
            $query->where('is_settled', 1);
        }

        $collections = $query->get();

        $this->totalCash = $collections->where('payment_type', 'cash')->sum('collection_amount');
        $this->totalNEFT = $collections->where('payment_type', 'neft')->sum('collection_amount');
        $this->totalCheque = $collections->where('payment_type', 'cheque')->sum('collection_amount');
        $this->totalDigital = $collections
            ->where('payment_type', 'digital_payment')
            ->sum(fn($item) => $item->collection_amount + $item->withdrawal_charge);
        
        $total = $this->totalCash + $this->totalNEFT + $this->totalCheque + $this->totalDigital;
        $this->totalWallet = "{$total} (Cash={$this->totalCash}, NEFT={$this->totalNEFT}, Cheque={$this->totalCheque}, Digi Payment={$this->totalDigital})";
    }

        public function submit()
    {
        if ($this->entry_type === 'given') {


            if ($this->collectedAmount <= 0) {
                $this->addError('collectedAmount', 'Please enter a valid given amount.');
                return;
            }

            try {
                DB::beginTransaction();

                DayCashEntryModel::create([
                    'staff_id'        => $this->staff_id,
                    'type'            => $this->entry_type,
                    'payment_date'    => now()->toDateString(),
                    'amount'          => $this->collectedAmount,
                    'payment_cash'    => $this->collectedAmount,
                    'payment_digital' => 0,
                ]);

               $payment = $this->createDebitRecords();
               
                $ledgerData = [
                    'staff_id' => $this->staff_id,
                    'amount'   => $this->collectedAmount,
                    'is_debit' => $this->entry_type === 'given' ? 1 : 0,  // ✅ given = debit
                    'is_credit'=> $this->entry_type === 'collect' ? 1 : 0, // ✅ collect = credit
                    'bank_cash'=> 'wallet',
                    'voucher_no'=> 'EXPENSE'.time(),
                    'purpose_description' => 'Given amount recorded',
                     'payment_id' => $payment ? $payment->id : "",
                     'transaction_id' => $payment ? $payment->voucher_no : "",
                ];
                $this->createLedgerRecord($ledgerData);
                
                DB::commit();

                $this->reset([
                    'collectedAmount', 'staff_id', 'entry_type'
                ]);

                session()->flash('success', 'Given amount recorded successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                session()->flash('error', 'Error: ' . $e->getMessage());
            }

            return;
        }

        // --- Handle collect type ---
        if (!$this->payment_cash && !$this->payment_digital) {
            $this->addError('payment_type', 'Please select at least one payment type.');
            return;
        }

        $cashAmount = floatval($this->cashCollectedAmount) ?? 0;
        $digitalAmount = floatval($this->digitalCollectedAmount) ?? 0;
        $totalAmount = $cashAmount + $digitalAmount;
        // NEW VALIDATION
        if ($this->entry_type === 'collect') {
            if ($this->payment_cash && $cashAmount > $this->totalCash) {
                $this->addError('cashCollectedAmount', 'Cash amount exceeds available cash.');
                return;
            }
            if ($this->payment_digital && $digitalAmount > $this->totalDigital) {
                $this->addError('digitalCollectedAmount', 'Digital amount exceeds available digital payments.');
                return;
            }
        }

        if ($totalAmount <= 0) {
            $this->addError('amount', 'Please enter a valid amount for at least one payment type.');
            return;
        }

        try {
            DB::beginTransaction();
            
            DayCashEntryModel::create([
                'staff_id'        => $this->staff_id,
                'type'            => $this->entry_type,
                'payment_date'    => now()->toDateString(),
                'amount'          => $totalAmount,
                'payment_cash'    => $cashAmount,
                'payment_digital' => $digitalAmount,
            ]);

            $this->settleCollections($cashAmount, $digitalAmount);

            $ledgerData = [
                'staff_id' => $this->staff_id,
                'amount'   => $totalAmount,
            'is_debit' => $this->entry_type === 'given' ? 1 : 0,  // ✅ given = debit
            'is_credit'=> $this->entry_type === 'collect' ? 1 : 0, // ✅ collect = credit
                'bank_cash'=> 'wallet',
                'voucher_no'=> 'EXPENSE'.time(),
                'purpose_description' => 'Given amount recorded',
            ];
            $this->createLedgerRecord($ledgerData);
            DB::commit();

            $this->reset([
                'cashCollectedAmount', 'digitalCollectedAmount', 
                'payment_cash', 'payment_digital'
            ]);
            
            session()->flash('success', 'Day cash entry submitted successfully!');
            return redirect()->route('admin.accounting.cashbook_module');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

   

    private function settleCollections($cashAmount, $digitalAmount)
    {
        // Settle cash payments
        if ($cashAmount > 0) {
            $this->settlePaymentType('cash', $cashAmount);
        }

        // Settle digital payments
        if ($digitalAmount > 0) {
            $this->settlePaymentType('digital_payment', $digitalAmount);
        }

        // Automatically settle cheque and NEFT payments
        PaymentCollection::where('user_id', $this->staff_id)
            ->where('is_approve', 1)
            ->where('is_settled', 0)
            ->whereIn('payment_type', ['cheque', 'neft'])
            ->update([
                'collection_amount' => 0,
                'withdrawal_charge' => 0, 
                'is_settled' => 1
            ]);

    }

   

    private function createLedgerRecord($data)
{
    // dd($data);
    // $data should include all required ledger columns
    $userTypeInt = User::where('id', $this->staff_id)->value('user_type') ?? 0;
    // Map integer to string for ledgers
    $userTypeMap = [
        0 => 'staff',
        1 => 'customer',
        // add partner/supplier if needed
    ];

    $userType = $userTypeMap[$userTypeInt] ?? 'staff'; // default to 'staff'


    Ledger::create([
        // 'id'                         => null, // auto increment
        'user_type'                  => $userType,
        'staff_id'                   => $data['staff_id'] ?? null,
        'customer_id'                => $data['customer_id'] ?? null,
        'supplier_id'                => $data['supplier_id'] ?? null,
        'admin_id'                   => auth()->id(),
        'payment_id'                 => $data['payment_id'] ?? null,
        'staff_commision_id'         => null,
        'collection_staff_commission_id' => null,
        'store_bad_debt_id'          => null,
        'transaction_id'             => $data['transaction_id'] ?? null,
        'invoice_no'                 => $data['invoice_no'] ?? $data['voucher_no'] ?? null,
        'voucher_no'                 => $data['voucher_no'] ?? null,
        'transaction_amount'         => $data['amount'] ?? 0,
        'is_credit'                  => $data['is_credit'] ?? 0,
        'is_debit'                   => $data['is_debit'] ?? 0,
        'bank_cash'                  => $data['bank_cash'] ?? 'wallet',
        'entry_date'                 => now(),
        'purpose'                    => $data['purpose'] ?? 'day_cash_entry',
        'purpose_description'        => $data['purpose_description'] ?? null,
        'start_date'                 => $data['start_date'] ?? null,
        'whatsapp_status'            => 0,
        'last_whatsapp'              => null,
        'created_at'                 => now(),
        'updated_at'                 => now(),
    ]);
}


   private function settlePaymentType($type, $amount)
{
    $collections = PaymentCollection::where('user_id', $this->staff_id)
        ->where('is_approve', 1)
        ->where('is_settled', 0)
        ->where('payment_type', $type)
        ->orderBy('id')
        ->get();

    $remaining = $amount;

    foreach ($collections as $collection) {
        if ($remaining <= 0) break;
        // dd($remaining);
        // For non-digital payments (cash, cheque, neft)
        if ($type !== 'digital_payment') {
            if ($remaining >= $collection->collection_amount) {
                // $remaining -= $collection->collection_amount;
                $collection->update([
                    // 'collection_amount' => 0,
                    'is_settled' => 1,
                ]);
            } else {
                $collection->update([
                    // 'collection_amount' => $collection->collection_amount - $remaining,
                    'is_settled' => 0,
                ]);
                $remaining = 0;
            }
        } 
        // For digital payments
        else {
            $totalDue = $collection->collection_amount + $collection->withdrawal_charge;
            if ($remaining >= $totalDue) {
                // Full settlement of both amount and charge
                $remaining -= $totalDue;
                $collection->update([
                //    'collection_amount' => 0,
                //    'withdrawal_charge' => 0, // This line ensures the charge is zeroed out
                   'is_settled' => 1,
                ]);
            } else {
                // Handle partial settlement
                if ($remaining >= $collection->collection_amount) {
                    // Settle collection amount first, then apply to withdrawal charge
                    $remaining -= $collection->collection_amount;
                    $chargeToSettle = min($remaining, $collection->withdrawal_charge);
                    
                    $collection->update([
                        'collection_amount' => 0,
                        'withdrawal_charge' => $collection->withdrawal_charge - $chargeToSettle,
                        'is_settled' => ($collection->withdrawal_charge - $chargeToSettle) === 0 ? 1 : 0,
                    ]);
                    $remaining = 0;
                } else {
                    // Partial payment of collection amount only
                    $collection->update([
                        // 'collection_amount' => $collection->collection_amount - $remaining,
                        'is_settled' => 0,
                    ]);
                    $remaining = 0;
                }
            }
        }
    }
}




    private function createDebitRecords()
    {
        $amount = $this->collectedAmount; 
        $timestamp = time();

        if ($amount > 0) {
           $payment =  Payment::create([
                'payment_for' => 'debit',
                'stuff_id'    => $this->staff_id,
                'amount'      => $amount,
                'payment_in'  => 'cash', // Or maybe make this dynamic if needed
                'voucher_no'  => 'EXPENSE' . $timestamp,
                'payment_date'=> now(),
                'payment_mode' => 'Cash',
                'created_from' => 'web',
                'is_ledger_added'=> 1,
                'is_approved' => 1,
                'approved_by' => auth('admin')->id(),
                'created_by' => auth('admin')->id()
                
            ]);
            // dd($payment);

            // Create journal entry
            Journal::create([
                'payment_id'        =>  $payment->id,
                'is_debit'           => 1,
                'transaction_amount' => $amount,
                'created_at'         => now(),
                'purpose'            => 'day_cash_entry',
                'purpose_description' => 'given',
                'purpose_id'         => $payment->voucher_no,
                'entry_date'         => $payment->payment_date
            ]);
            
            return $payment;
        }
        return null;
    }


    public function render()
    {
        return view('livewire.accounting.day-cash-entry');
    }
}