<?php

namespace App\Http\Livewire\Supplier;

use App\Models\Supplier;
use App\Models\Country;
use App\Models\UserWhatsapp;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;


class SupplierAdd extends Component
{
    use WithFileUploads;

    public $name, $email, $phone, $whatsapp_no ,$alternative_phone_number_1, $alternative_phone_number_2;
    public $billing_address, $billing_landmark, $billing_state, $billing_city, $billing_pin, $billing_country;
    public $gst_number, $gst_file, $credit_limit, $credit_days;
    protected $rules=[];
    public $countries = [];
    public $selectedCountryId;
    public $country_code;
    public $mobileLength;
    public $phone_code,$alt_phone_code_1,$alt_phone_code_2,
            $mobileLengthPhone,   $mobileLengthWhatsapp  ,  $mobileLengthAlt1, $mobileLengthAlt2;
    public $isWhatsappPhone, $isWhatsappAlt1,$isWhatsappAlt2;
    public $supplier;

    public function CountryCodeSet($selector, $Code, $number = null)
    {
        $mobile_length = Country::where('country_code', $Code)->value('mobile_length') ?? '';

        // Dispatch for maxlength
        $this->dispatch('update_input_max_length', [
            'id' => $selector,
            'mobile_length' => $mobile_length
        ]);

        $this->mobileLengthPhone = $mobile_length;
    }
   public function rules(){
     return [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('suppliers', 'email')->ignore(optional($this->supplier)->id),
            ],
            'phone' => [
                'required',
               'regex:/^\d{'. $this->mobileLengthPhone .'}$/',
            ],
            // 'whatsapp_no' => [
            //     'required',
            //     'regex:/^\d{'. $this->mobileLengthWhatsapp .'}$/',
            // ],
            'alternative_phone_number_1' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt1 .'}$/',
            ],
            'alternative_phone_number_2' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt2 .'}$/',
            ],
            'billing_address' => 'required|string|max:255',
            'billing_landmark' => 'nullable|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pin' => 'nullable|string|max:10',
            'billing_country' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'gst_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:1024',
            'credit_limit' => 'nullable|numeric',
            'credit_days' => 'nullable|numeric',
        ];    
   }
      
   public function messages()
    {
        return [
            // 'searchTerm.required' => 'Please select a country.',
            // 'name.required' => 'Supplier name is required.',
            'email.email' => 'Enter a valid email address.',
            'phone.required' => 'Mobile number is required.',
            'phone.regex' => 'Mobile number must be exactly ' . $this->mobileLengthPhone . ' digits.',
            // 'whatsapp_no.required' => 'WhatsApp number is required.',
            // 'whatsapp_no.regex' => 'WhatsApp number must be exactly ' . $this->mobileLengthWhatsapp . ' digits.',
            'alternative_phone_number_1.regex' => 'Alternative phone number 1 must be exactly ' . $this->mobileLengthAlt1 . ' digits.',
            'alternative_phone_number_2.regex' => 'Alternative phone number 2 must be exactly ' . $this->mobileLengthAlt2 . ' digits.',
            'billing_address.required' => 'Billing address is required.',
            'billing_state.required' => 'Billing state is required.',
            'billing_city.required' => 'Billing city is required.',
            'billing_country.required' => 'Billing country is required.',
            'gst_file.mimes' => 'GST file must be a PDF, JPEG, PNG, or JPG.',
            'gst_file.max' => 'GST file must not exceed 1MB.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
            'credit_days.numeric' => 'Credit days must be a number.',
        ];
    }
   
    public function save()
    {   
        
        
        $this->validate();
        try{
            if ($this->gst_file) {
                $gstFilePath = $this->gst_file->store('gst_files','public');
                $absoluteAssetPath = 'storage/' . $gstFilePath;
            }
        
        $supplier =  Supplier::create([
                // 'prefix' => $this->prefix,
                'name' => $this->name,
                'email' => $this->email,
                // 'country_code'=> $this->country_code,
                'country_code_alt_1' => $this->alt_phone_code_1,
                'alternative_phone_number_1' => $this->alternative_phone_number_1,
                'country_code_alt_2'  => $this->alt_phone_code_2,
                'alternative_phone_number_2' => $this->alternative_phone_number_2,
                'country_code_mobile' => $this->phone_code,
                'mobile' => $this->phone,
                // 'country_code_whatsapp'=> $this->phone_code,
                // 'whatsapp_no' => $this->whatsapp_no,
                'billing_address' => $this->billing_address,
                'billing_landmark' => $this->billing_landmark,
                'billing_state' => $this->billing_state,
                'billing_city' => $this->billing_city,
                'billing_pin' => $this->billing_pin,
                'billing_country' => $this->billing_country,
                'gst_number' => $this->gst_number,
                'gst_file' => isset($absoluteAssetPath) ? $absoluteAssetPath : null, 
                'credit_limit' => $this->credit_limit,
                'credit_days' => $this->credit_days,
            ]);
        
       

        if ($this->isWhatsappPhone) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->phone)->first();
        
            if (!$existingRecord) {
                UserWhatsapp::create([
                    'supplier_id' => $supplier->id,
                    'whatsapp_number' => $this->phone,
                    'country_code' => $this->phone_code,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($existingRecord->supplier_id != $supplier->id) {
                // Optionally update the record if the supplier ID is different
                $existingRecord->update([
                    'supplier_id' => $supplier->id,
                    'country_code' => $this->phone_code,
                    'updated_at' => now()
                ]);
            }
        }
        
        if ($this->isWhatsappAlt1) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_1)->first();
        
            if (!$existingRecord) {
                UserWhatsapp::create([
                    'supplier_id' => $supplier->id,
                    'whatsapp_number' => $this->alternative_phone_number_1,
                    'country_code' => $this->alt_phone_code_1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($existingRecord->supplier_id != $supplier->id) {
                $existingRecord->update([
                    'supplier_id' => $supplier->id,
                    'country_code' => $this->alt_phone_code_1,
                    'updated_at' => now()
                ]);
            }
        }
        
        if ($this->isWhatsappAlt2) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_2)->first(); // FIXED: Correct phone number
        
            if (!$existingRecord) {
                UserWhatsapp::create([
                    'supplier_id' => $supplier->id,
                    'whatsapp_number' => $this->alternative_phone_number_2,
                    'country_code' => $this->alt_phone_code_2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($existingRecord->supplier_id != $supplier->id) {
                $existingRecord->update([
                    'supplier_id' => $supplier->id,
                    'country_code' => $this->alt_phone_code_2,
                    'updated_at' => now()
                ]);
            }
        }
        
        
            session()->flash('success', 'Supplier added successfully!');
            return redirect()->route('suppliers.index');
        }catch(\Exception $e){
             // Catch any error and store it in the session
            session()->flash('error', 'Something went wrong! ' . $e->getMessage());
            dd($e->getMessage());
            return back()->withInput();
        }

    }

    
    public function render()
    {
        return view('livewire.supplier.supplier-add');
    }
}
