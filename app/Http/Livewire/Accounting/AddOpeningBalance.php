<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\User;
use App\Helpers\Helper;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Interfaces\AccountingRepositoryInterface;


class AddOpeningBalance extends Component
{   
    protected $accountingRepository;
    public $customer,$customer_id,$credit_debit,$payment_type,$date,$voucher_no,$bank_amount,$amount,$cash_amount,$bank_name,$payment_mode,$transaction_no,$narration;
    public $searchResults = [];
    public $showCashAmount = false;
    public $showBankFields  = true;
    public $showPaymentMode  = true;
    public $errorMessage = [];

    public function boot(AccountingRepositoryInterface $accountingRepository){
        $this->accountingRepository = $accountingRepository;
    }

    public function mount(){
        $this->voucher_no = 'PAYRECEIPT'.time();
    }

    public function findCustomer($term){
        $this->searchResults = Helper::GetCustomerDetails($term);
    }

    public function selectCustomer($customer_id){
        $customer = User::find($customer_id);
        if($customer){
            $this->customer = $customer->name.'('.$customer->phone.')';
            $this->customer_id = $customer->id;
        }else{
            $this->reset(['customer','customer_id',]);
        }

        $this->searchResults = [];
    }

    public function updateCreditDebit($value){
        $this->credit_debit = $value;
    }

    public function UpdatePaymentType($value){
        if($value == "bank"){
            $this->showBankFields = true;
            $this->showCashAmount = false;
            $this->showPaymentMode = true;
        }elseif($value == 'cash'){
            $this->showBankFields = true;
            $this->showCashAmount = false;
            $this->showPaymentMode = false;
        }elseif($value == 'bank_cash'){
            $this->showBankFields = false;
            $this->showCashAmount = true;
            $this->showPaymentMode = true;
        }
    }

    public function submitForm(){
        $this->reset(['errorMessage']);
        $this->errorMessage = array();
        if (empty($this->customer_id)) {
            $this->errorMessage['customer_id'] = 'Please select a customer';
        }
        
        if(empty($this->credit_debit)){
            $this->errorMessage['credit_debit'] = 'Please select Credit or Debit';
        }

        if(empty($this->payment_type)){
            $this->errorMessage['payment_type'] = 'Please select a payment type';
        }

        if(empty($this->date)){
            $this->errorMessage['date'] = 'Please select a valid date';
        }

        if(empty($this->voucher_no)){
            $this->errorMessage['voucher_no'] = 'Voucher number is required';
        }

        //  Only validate `amount` for `bank` or `cash`, NOT `bank_cash`
        if(empty($this->amount) && ($this->payment_type === 'bank' || $this->payment_type === 'cash')){
            $this->errorMessage['amount'] = 'Please enter the amount';
        }
        
        //Validate Bank + Cash amounts separately
        if($this->payment_type === 'bank_cash'){
            if(empty($this->bank_amount)){
                $this->errorMessage['bank_amount'] = 'Please enter the bank amount';
            }
            if(empty($this->cash_amount)){
                $this->errorMessage['cash_amount'] = 'Please enter the cash amount';
            }
        }

        if(empty($this->payment_mode) && $this->payment_type === 'bank'){
            $this->errorMessage['payment_mode'] = 'Please select a payment mode';
        }

        if(count($this->errorMessage)> 0){
            return $this->errorMessage;
        }

            $data = [
                'customer_id'=> $this->customer_id,
                'credit_debit'  => $this->credit_debit,
                'payment_type'  => $this->payment_type,
                'date'          => $this->date,
                'voucher_no'    => $this->voucher_no,
                'amount'        => $this->amount,
                'bank_amount'   => $this->bank_amount ?? null,
                'cash_amount'   => $this->cash_amount ?? null,
                'bank_name'     => $this->bank_name ?? null,
                'payment_mode'  => $this->payment_mode ?? null,
                'transaction_no'=> $this->transaction_no ?? null,
                'narration'     => $this->narration ?? null,

            ];
            
            try {
                DB::beginTransaction();
                $data = $this->accountingRepository->StoreOpeningBalance($this->all());
                if($data['status']==false){
                    session()->flash('error', $data['message']);
                }else{
                    DB::commit();
                    session()->flash('success', $data['message']);
                    return redirect()->route('admin.accounting.list_opening_balance');
                }
                
            } catch (\Exception $e) {
                // dd($e->getMessage());
                DB::rollBack();
                session()->flash('error', $e->getMessage());
            }
        
    }


    public function render()
    {
        return view('livewire.accounting.add-opening-balance');
    }
}
