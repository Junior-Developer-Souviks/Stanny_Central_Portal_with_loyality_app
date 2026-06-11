<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentCollection;
use App\Models\Country;
use App\Models\TodoList;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Interfaces\AccountingRepositoryInterface;

class AddPaymentReceipt extends Component
{
    protected $accountingRepository;
    public $searchResults = [];
    public $errorClass = [];
    public $errorMessage = [];
    public $activePayementMode = 'cash';
    public $staffs =[];
    public $my_designation;
    public $payment_voucher_no;
    public $payment_id;
    public $new_customer = false;
    public $payment_collection_id = "";
    public $readonly = "readonly";
    public $customer,$customer_id, $customer_name, $staff_id, $amount, $voucher_no, $payment_date,$next_payment_date,$credit_date,$deposit_date,$payment_mode, $chq_utr_no, $bank_name, $receipt_for = "Customer",$cheque_photo,$cheque_file,$transaction_no;
    public $mobileLengthPhone,$countries,$phone_code,$phone,$customer_email,$customer_company,$customer_address,$withdrawal_charge,$payment_data;
    use WithFileUploads;

    public function boot(AccountingRepositoryInterface $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }
    public function mount($payment_voucher_no=""){
        $this->customer_id = request()->get('customer_id');
        if(isset($this->customer_id)){
            $user = User::findOrFail($this->customer_id);
            $this->customer = $user->name;
        }
        $user = Auth::guard('admin')->user();
        $this->my_designation = $user->designation;
        $payment_collection = PaymentCollection::with('customer', 'user')->where('voucher_no',$payment_voucher_no)->first();
        $this->payment_collection_id = !empty($payment_collection)?$payment_collection->id:'';
        $this->payment_data= $payment_collection;
        if(!empty($payment_voucher_no)){
            if(!$payment_collection){
                abort(404);
                return false;
            }
        }

        $this->payment_voucher_no = $payment_voucher_no;
        $this->voucher_no = 'PAYRECEIPT'.time();
         if($user->designation == 1){
            $this->staffs = User::where('user_type', 0)->whereIn('designation', [2,12])->select('name', 'id','designation')->orderBy('name', 'ASC')->get();
        }else{
            $this->staffs = collect([$user]); // Only themselves
            $this->staff_id = $user->id;
        }
        if($payment_collection){
            $this->payment_collection_id = $payment_collection->id;
            $this->customer = $payment_collection->customer->name;
            $this->customer_id = $payment_collection->customer_id;
            $this->staff_id = $payment_collection->user_id;
            $this->amount = $payment_collection->collection_amount;
            $this->voucher_no = $payment_collection->voucher_no;
            $this->payment_date = $payment_collection->cheque_date;
            $this->payment_mode = $payment_collection->payment_type;
            $this->chq_utr_no = $payment_collection->cheque_number;
            $this->bank_name = $payment_collection->bank_name;
            $this->payment_id = $payment_collection->payment_id;
            $this->activePayementMode = $payment_collection->payment_type;
        }
        if(empty($payment_voucher_no)){
            $this->readonly = "";
        }
    }
    public function CountryCodeSet($selector, $Code, $number = null)
    {
        $mobile_length = Country::where('country_code', $Code)->value('mobile_length') ?? '8';

        // Dispatch for maxlength
        $this->dispatch('update_input_max_length', [
            'id' => $selector,
            'mobile_length' => $mobile_length
        ]);
    }
    public function rules()
    {
       $rules = [];
    if ($this->payment_mode === 'cheque' and empty($this->payment_collection_id)) {
        $rules['deposit_date'] = 'required'; // Optional: adjust validation as needed
        $rules['cheque_file'] = 'required|image|max:5120';


    }
     else if ($this->payment_mode === 'digital_payment') {
        $rules['withdrawal_charge'] = 'required | numeric'; // Optional: adjust validation as needed
        $rules['transaction_no'] = 'required'; // Optional: adjust validation as needed

    }
    else if (!empty($this->payment_collection_id)) {
        $rules['credit_date'] = 'required'; // Optional: adjust validation as needed

    }


    return $rules;
    }
    public function GetCountryDetails($mobileLength, $field){
        switch($field){
            case 'phone':
                $this->mobileLengthPhone  = $mobileLength;
                break;
        }
    }

    public function changeNewCustomer(){
       $this->dispatch('update_input_phone');
    }

    public function submitForm()
    {

        $this->mobileLengthPhone = Country::where('country_code', $this->phone_code)->value('mobile_length') ?? '8';
        $this->reset(['errorMessage']);

        $this->errorMessage = array();
         if ($this->payment_mode === 'cheque' or $this->payment_mode === 'digital_payment') {
        $this->validate();
         }
        // Validate customer
        if($this->new_customer){
            if (empty($this->customer_name)) {
                $this->errorMessage['customer_name'] = 'Please enter customer name.';
            }
             if (empty($this->phone)) {
                $this->errorMessage['phone'] = 'Please enter mobile number.';
            } elseif (!ctype_digit($this->phone)) {
                $this->errorMessage['phone'] = 'Phone number must contain only digits.';
            } elseif (strlen($this->phone) != $this->mobileLengthPhone) {
                $this->errorMessage['phone'] = 'Phone number must be exactly ' . $this->mobileLengthPhone . ' digits.';
            } elseif (User::where('phone', $this->phone)->whereNull('deleted_at')->exists()) {
                $this->errorMessage['phone'] = 'This phone number is already in use.';
            }
        }else{
            if (empty($this->customer_id)) {
                $this->errorMessage['customer_id'] = 'Please select a customer.';
            }
        }


        // Validate collected by
        if (empty($this->staff_id)) {
           $this->errorMessage['staff_id'] = 'Please select a staff member.';
        }

        // Validate amount
        if (empty($this->amount) || !is_numeric($this->amount)) {
           $this->errorMessage['amount'] = 'Please enter a valid amount.';
        }

        // Validate voucher no
        if (empty($this->voucher_no)) {
           $this->errorMessage['voucher_no'] = 'Please enter a voucher number.';
        }

        // Validate payment date
        if (empty($this->payment_date) || !$this->is_valid_date($this->payment_date)) {
           $this->errorMessage['payment_date'] = 'Please select a valid payment date.';
        }

        // Validate payment mode
        if (empty($this->payment_mode)) {
           $this->errorMessage['payment_mode'] = 'Please select a payment mode.';
        }

        // Validate cheque no / UTR no
        if ($this->payment_mode != 'cash' && empty($this->chq_utr_no) && $this->payment_mode != 'digital_payment') {
           $this->errorMessage['chq_utr_no'] = 'Please enter a cheque no / UTR no.';
        }

        // Validate bank name
        if ($this->payment_mode != 'cash' && empty($this->bank_name)) {
           $this->errorMessage['bank_name'] = 'Please enter a bank name.';
        }
        if(count($this->errorMessage)>0){
            return $this->errorMessage;
        }else{
            try {
                DB::beginTransaction();
                if($this->new_customer){
                    $user = new User;
                    $user->name = ucwords($this->customer_name);
                    $user->country_code_phone = $this->phone_code;
                    $user->phone = $this->phone;
                    $user->email = $this->customer_email ?? null;
                    $user->company_name = $this->customer_company ?? null;
                    $user->location = $this->customer_address ?? null;
                    $user->user_type = 1; // Assuming 1 is for customers
                    $user->save();
                    $this->customer_id = $user->id;
                }

                //code...
                if($this->payment_mode === 'cheque')
                {
                    $extension = $this->cheque_file->getClientOriginalExtension();
                    $filename = Str::random(10) . '.' . $extension;
                    $path = $this->cheque_file->storeAs('uploads/cheque', $filename, 'public');
                    $publicPath = 'storage/' . $path;
                    $this->cheque_photo=$publicPath;

                }
                $this->accountingRepository->StorePaymentReceipt($this->all());
                $admin_id = Auth::guard('admin')->user()->id;
                if(!empty($this->next_payment_date))
                {
                        $todoData=[
                            'user_id'=>$this->staff_id,
                            'customer_id'=>$this->customer_id,
                            'created_by'=>$admin_id,
                            'todo_type'=>'Payment',
                            'todo_date'=>$this->next_payment_date,
                            'remark'=>'Next Payment Schedule on '.$this->next_payment_date
                        ];
                        TodoList::insertGetId($todoData);

                }
                 if(!empty($this->deposit_date))
                {
                        $todoData=[
                            'user_id'=>$this->staff_id,
                            'customer_id'=>$this->customer_id,
                            'created_by'=>$admin_id,
                            'todo_type'=>'Cheque Deposit',
                            'todo_date'=>$this->deposit_date,
                            'remark'=>'Deposit Date '.$this->deposit_date
                        ];
                      TodoList::insertGetId($todoData);


                }
                session()->flash('success', 'Payment receipt added successfully.');
                DB::commit();
                return redirect()->route('admin.accounting.payment_collection');
            } catch (\Exception $e) {
                DB::rollBack();
                session()->flash('error', $e->getMessage());
            }
        }

    }
    public function ResetForm(){
        $this->reset(['customer','customer_id','staff_id', 'amount', 'voucher_no', 'payment_date', 'payment_mode', 'chq_utr_no', 'bank_name',
        'cheque_file','credit_date','deposit_date']);
        $this->voucher_no = 'PAYRECEIPT'.time();
    }

    public function FindCustomer($term)
    {
        $this->searchResults = Helper::GetCustomerDetails($term);
    }
    // Function to validate date
    public function is_valid_date($date) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return true;
        }
        return false;
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

    public function ChangePaymentMode($value){
        $this->activePayementMode = $value;
    }

    public function render()
    {
        if(empty($this->staff_id)){
            $this->staff_id = Auth::guard('admin')->user()->id;
        }

        $this->payment_date=date('Y-m-d');
        if(!empty($this->payment_data))
        {
            return view('livewire.accounting.update-cheque');
        }
        else{
            return view('livewire.accounting.add-payment-receipt');
        }
    }
    public function editReceipt()
    {
        try {
        $this->validate();

            $payment_collection = PaymentCollection::find($this->payment_collection_id); // Replace 'User' with your model name
            $payment_collection->credit_date = $this->credit_date; // Replace 'name' with your field
            $payment_collection->save();
            session()->flash('success', 'Credit Date  Added successfully.');
            return redirect()->route('admin.accounting.payment_collection');
        } catch (\Exception $e) {
           session()->flash('error', $e->getMessage());
        }
    }
}
