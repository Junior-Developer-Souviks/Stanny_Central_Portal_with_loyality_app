<?php

namespace App\Http\Livewire\Supplier;

use App\Models\Supplier;
use App\Models\Country;
use App\Models\UserWhatsapp;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupplierEdit extends Component
{
    use WithFileUploads;

    public $supplier, $name, $email, $phone, $is_wa_same, $whatsapp_no;
    public $billing_address, $billing_landmark, $billing_state, $billing_city, $billing_pin, $billing_country;
    public $gst_number, $gst_file, $credit_limit, $credit_days;
    public $mobileLength;
    public $countries;
    // public $searchTerm;
    public $existingGstFile;
    public $country_code;
    public $selectedCountryId;
    public $alternative_phone_number_1;
    public $alternative_phone_number_2;
    public $phone_code , $selectedCountryWhatsapp, $alt_phone_code_1, $alt_phone_code_2,
           $mobileLengthPhone , $mobileLengthAlt1, $mobileLengthAlt2;
    public $isWhatsappPhone, $isWhatsappAlt1, $isWhatsappAlt2;


    public function mount($id)
    {
        $this->supplier = Supplier::find($id);
        // $this->prefix = $this->supplier->prefix;
        $this->name = $this->supplier->name;
        $this->email = $this->supplier->email;
        $this->phone = $this->supplier->mobile;
        // $this->is_wa_same = ($this->supplier->mobile == $this->supplier->whatsapp_no) ? 1 : 0;
        $this->whatsapp_no = $this->supplier->whatsapp_no;
        $this->billing_address = $this->supplier->billing_address;
        $this->billing_landmark = $this->supplier->billing_landmark;
        $this->billing_state = $this->supplier->billing_state;
        $this->billing_city = $this->supplier->billing_city;
        $this->billing_pin = $this->supplier->billing_pin;
        $this->billing_country = $this->supplier->billing_country;
        $this->gst_number = $this->supplier->gst_number;
        $this->credit_limit = $this->supplier->credit_limit;
        $this->credit_days = $this->supplier->credit_days;
        // Set the existing GST file path
        $this->existingGstFile = $this->supplier->gst_file;
        $this->alternative_phone_number_1 = $this->supplier->alternative_phone_number_1;
        $this->alternative_phone_number_2 = $this->supplier->alternative_phone_number_2;

        $this->phone_code = $this->supplier->country_code_mobile;
        $this->selectedCountryWhatsapp = $this->supplier->country_code_whatsapp;
        $this->alt_phone_code_1 = $this->supplier->country_code_alt_1;
        $this->alt_phone_code_2 = $this->supplier->country_code_alt_2;

        $this->mobileLengthPhone = Country::where('country_code',$this->phone_code)->value('mobile_length') ?? '';
        $this->mobileLengthAlt1 = Country::where('country_code',$this->alt_phone_code_1)->value('mobile_length') ?? '';
        $this->mobileLengthAlt2 = Country::where('country_code',$this->alt_phone_code_2)->value('mobile_length') ?? '';

        $this->isWhatsappPhone = UserWhatsapp::where('supplier_id',$this->supplier->id)->where('whatsapp_number',$this->phone)->exists();
        $this->isWhatsappAlt1 =  UserWhatsapp::where('supplier_id',$this->supplier->id)->where('whatsapp_number',$this->alternative_phone_number_1)->exists();
        $this->isWhatsappAlt2 =  UserWhatsapp::where('supplier_id',$this->supplier->id)->where('whatsapp_number',$this->alternative_phone_number_2)->exists();

    }

   public function CountryCodeSet($selector, $Code, $number = null)
    {
        $mobile_length = Country::where('country_code', $Code)->value('mobile_length') ?? '';

        // Dispatch for maxlength
        $this->dispatch('update_input_max_length', [
            'id' => $selector,
            'mobile_length' => $mobile_length
        ]);
    }
    public function updateSupplier()
    {
        // dd($this->all());
        $this->validate([
            // 'searchTerm' => 'required',
            // 'prefix' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $this->supplier->id,
            'phone' => [
                'required',
                'regex:/^\d{'. $this->mobileLengthPhone .'}$/',
            ],
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
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_pin' => 'nullable|string|max:10',
            'billing_country' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'gst_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:1024',
            'credit_limit' => 'nullable|numeric',
            'credit_days' => 'nullable|numeric',
        ],[
            // 'searchTerm.required' => 'Please select a country.',
            'name.required' => 'Supplier name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Mobile number is required.',
            'phone.regex' => 'Mobile number must be exactly ' . $this->mobileLengthPhone . ' digits.',
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
        ]);

        if ($this->gst_file) {
            $gstFilePath = $this->gst_file->store('gst_files','public');
            $absolutePath = 'storage/' . $gstFilePath;
        }

        $this->supplier->update([
            // 'prefix' => $this->prefix,
            'name' => $this->name,
            'email' => $this->email,
            'country_code_mobile' => $this->phone_code,
            'mobile' => $this->phone,
            // 'whatsapp_no' => $this->whatsapp_no,
            'billing_address' => $this->billing_address,
            'billing_landmark' => $this->billing_landmark,
            'billing_state' => $this->billing_state,
            'billing_city' => $this->billing_city,
            'billing_pin' => $this->billing_pin,
            'billing_country' => $this->billing_country,
            'gst_number' => $this->gst_number,
            'gst_file' => isset($absolutePath) ? $absolutePath :  $this->supplier->gst_file, // Use the new file path or the existing one
            'credit_limit' => $this->credit_limit,
            'credit_days' => $this->credit_days,
            'country_id' => $this->selectedCountryId,
            // 'country_code' => $this->country_code,
            'country_code_alt_1'  => $this->alt_phone_code_1,
            'alternative_phone_number_1' => $this->alternative_phone_number_1,
            'country_code_alt_2'  => $this->alt_phone_code_2,
            'alternative_phone_number_2' => $this->alternative_phone_number_2,

        ]);

        if ($this->isWhatsappPhone) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->phone)
                                                ->where('supplier_id','!=', $this->supplier->id)
                                                ->exists();
        if(!$existingRecord){
            UserWhatsapp::updateOrCreate(
                ['supplier_id' => $this->supplier->id, 'whatsapp_number' => $this->phone],
                ['country_code' => $this->phone_code, 'updated_at' => now()]
            ); 
          }
        }else {
            if(!empty($this->phone)){
                UserWhatsapp::where('supplier_id', $this->supplier->id)->where('whatsapp_number', $this->phone)->delete();
            }
        }


        
        if ($this->isWhatsappAlt1) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_1)
                                                ->where('supplier_id','!=', $this->supplier->id)
                                                ->exists();
        if(!$existingRecord){
            UserWhatsapp::updateOrCreate(
                ['supplier_id' =>  $this->supplier->id, 'whatsapp_number' => $this->alternative_phone_number_1],
                ['country_code' => $this->alt_phone_code_1, 'updated_at' => now()]
            );
        }
        }else {
            if(!empty($this->alternative_phone_number_1)){
                UserWhatsapp::where('supplier_id', $this->supplier->id)->where('whatsapp_number', $this->alternative_phone_number_1)->delete();
            }
        }
    
        
        if ($this->isWhatsappAlt2) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_2)
                                                ->where('supplier_id','!=', $this->supplier->id)
                                                ->exists();
        if(!$existingRecord){
            UserWhatsapp::updateOrCreate(
                ['supplier_id' =>  $this->supplier->id, 'whatsapp_number' => $this->alternative_phone_number_2],
                ['country_code' => $this->alt_phone_code_2, 'updated_at' => now()]
            );
        }
        }else {
            if(!empty($this->alternative_phone_number_2)){
                UserWhatsapp::where('supplier_id', $this->supplier->id)->where('whatsapp_number', $this->alternative_phone_number_2)->delete();
            }
        }
    

        session()->flash('success', 'Supplier updated successfully!');
        return redirect()->route('suppliers.index');
    }

   

    public function render()
    {
        return view('livewire.supplier.supplier-edit');
    }
}
