<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Country;
use App\Models\UserAddress;
use App\Models\UserWhatsapp;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

class CustomerEdit extends Component
{
    use WithFileUploads;

    public $id, $name,$dob, $company_name, $employee_rank,$email, $phone, $whatsapp_no, $gst_number, $credit_limit, $credit_days, $gst_certificate_image, $image, $verified_video ,$alternative_phone_number_1, $alternative_phone_number_2,
    $phone_code, $alt_phone_code_1 , $alt_phone_code_2 ,$mobileLengthPhone, $mobileLengthAlt1, $mobileLengthAlt2,
    $countries,
     $isWhatsappPhone, $isWhatsappAlt1 , $isWhatsappAlt2;
    public $billing_address, $billing_landmark, $billing_city, $billing_state, $billing_country, $billing_pin;
    public $shipping_address, $shipping_landmark, $shipping_city, $shipping_state, $shipping_country, $shipping_pin;
    public $is_billing_shipping_same;
    public $tempImageUrl;
    public $mobileLength;
    public $country_id;
    public $searchTerm;
    public $country_code;
    public $prefix;
    public $badge_type;

    public function mount($id)
    {
        if ($id) {
            $this->id = $id;
            $user = User::find($id);

            $this->fillUserData($user);

            $billingAddress = $user->address()->where('address_type', 1)->first();
            $shippingAddress = $user->address()->where('address_type', 2)->first();

            $this->fillAddressData($billingAddress, $shippingAddress);
            
            $this->alternative_phone_number_1 = $user->alternative_phone_number_1;
            $this->alternative_phone_number_2 = $user->alternative_phone_number_2;
            $this->phone = $user->phone;
            $this->badge_type = $user->customer_badge;
            $this->countries = Country::where('status',1)->get();
            $this->phone_code = $user->country_code_phone;
            $this->alt_phone_code_1 = $user->country_code_alt_1;
            $this->alt_phone_code_2 = $user->country_code_alt_2;

            // Set mobile lengths based on selected countries
            $this->mobileLengthPhone = Country::where('country_code', $this->phone_code)->value('mobile_length') ?? '8';
            $this->mobileLengthAlt1 = Country::where('country_code', $this->alt_phone_code_1)->value('mobile_length') ?? '8';
            $this->mobileLengthAlt2 = Country::where('country_code', $this->alt_phone_code_2)->value('mobile_length') ?? '8';
            
            $this->isWhatsappPhone = UserWhatsapp::where('user_id',$user->id)->where('whatsapp_number',$this->phone)->exists();

            $this->isWhatsappAlt1 = UserWhatsapp::where('user_id',$user->id)->where('whatsapp_number',$this->alternative_phone_number_1)->exists();

            $this->isWhatsappAlt2 = UserWhatsapp::where('user_id',$user->id)->where('whatsapp_number',$this->alternative_phone_number_2)->exists();
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

    public function GetCountryDetails($mobileLength, $field){
        switch($field){
            case 'phone':
                $this->mobileLengthPhone  = $mobileLength;
                break;

            case 'alt_phone_1':
                $this->mobileLengthAlt1 = $mobileLength;
                break;
            
            case 'alt_phone_2':
                $this->mobileLengthAlt2 = $mobileLength;
                break;
            
                
        }
    }
   
    
    private function fillUserData($user)
    {
        $this->prefix = $user->prefix ?? "";
        $this->name = $user->name ?? "";
        $this->company_name = $user->company_name ?? "";
        $this->employee_rank = $user->employee_rank ?? "";
        $this->email = $user->email ?? "";
        $this->dob   = $user->dob ?? null;  
        $this->gst_number = $user->gst_number ?? "";
        $this->credit_limit = $user->credit_limit ?? "";
        $this->credit_days = $user->credit_days ?? "";
        $this->image = $user->profile_image ? asset( $user->profile_image) : "";
        $this->gst_certificate_image = $user->gst_certificate_image ? asset( $user->gst_certificate_image) : "";

    }

    private function fillAddressData($billingAddress)
    {
        if ($billingAddress) {
            $this->billing_address = $billingAddress->address;
            $this->billing_landmark = $billingAddress->landmark;
            $this->billing_city = $billingAddress->city;
            $this->billing_state = $billingAddress->state;
            $this->billing_country = $billingAddress->country;
            $this->billing_pin = $billingAddress->zip_code;
        }

    }
    

    public function rules()
    {
        $id = $this->id ?? null; // assuming you set current user id in component/controller

        $rules = [
            'name' => 'required|string|max:255',
            'employee_rank'=>'nullable|string',
            'image' => $this->image instanceof \Illuminate\Http\UploadedFile 
                ? 'nullable|mimes:jpg,jpeg,png,gif' 
                : 'nullable',
            'company_name' => 'nullable|string|max:255',

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($id) // ignore current record
                    ->whereNull('deleted_at'),
            ],

            'dob'=> 'nullable|date',

            'phone' => [
                'required',
                'regex:/^\d{'. $this->mobileLengthPhone .'}$/',
                Rule::unique('users', 'phone')
                    ->ignore($id) // ignore current record
                    ->whereNull('deleted_at'),
            ],

            'alternative_phone_number_1' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt1 .'}$/',
            ],
            'alternative_phone_number_2' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt2 .'}$/',
            ],

            'gst_number' => 'nullable|string|max:15',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',

            'billing_address' => 'required|string',
            'billing_landmark' => 'nullable|string',
            'billing_city' => 'required|string',
            'billing_state' => 'nullable|string',
            'billing_country' => 'required|string',
            'billing_pin' => 'nullable|string',
        ];

        return $rules;
    }

    public function update()
    {
        // dd($this->all());
         // Prepare data for dd and avoid showing existing image/verified_video
        $dataToLog = $this->all();

        // Check if 'image' exists already and unset it from the log data
        if (isset($this->image) && !empty($this->image)) {
            // Don't log image data if it already exists
            unset($dataToLog['image']);
        }

        // Check if 'verified_video' exists already and unset it from the log data
        if (isset($this->verified_video) && !empty($this->verified_video)) {
            // Don't log verified_video data if it already exists
            unset($dataToLog['verified_video']);
        }

        // Log data without the image and verified_video if they exist
        // dd($dataToLog);
        $this->validate();
      
        $user = User::find($this->id);
        $user->fill($this->prepareUserData());

        if ($this->isWhatsappPhone) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->phone)
                                                    ->where('user_id', '!=', $user->id)
                                                    ->exists();
        if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $user->id, 'whatsapp_number' => $this->phone],
                    ['country_code' => $this->phone_code, 'updated_at' => now()]
                ); 
            }
        }else {
            if(!empty($this->phone)){
                UserWhatsapp::where('user_id', $user->id)->where('whatsapp_number', $this->phone)->delete();
            }
        }

        
        if ($this->isWhatsappAlt1) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_1)
                                                    ->where('user_id', '!=', $user->id)
                                                    ->exists();
        if(!$existingRecord){
            UserWhatsapp::updateOrCreate(
                ['user_id' => $user->id, 'whatsapp_number' => $this->alternative_phone_number_1],
                ['country_code' => $this->alt_phone_code_1, 'updated_at' => now()]
            );
        }
        }else {
            if(!empty($this->alternative_phone_number_1)){
                UserWhatsapp::where('user_id', $user->id)->where('whatsapp_number', $this->alternative_phone_number_1)->delete();
            }
        }

        
        if ($this->isWhatsappAlt2) {
            $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_2)
                                                    ->where('user_id', '!=', $user->id)
                                                    ->exists();
        if(!$existingRecord){
            UserWhatsapp::updateOrCreate(
                ['user_id' => $user->id, 'whatsapp_number' => $this->alternative_phone_number_2],
                ['country_code' => $this->alt_phone_code_2, 'updated_at' => now()]
            );
        }
        }else {
            if(!empty($this->alternative_phone_number_2)){
                UserWhatsapp::where('user_id', $user->id)->where('whatsapp_number', $this->alternative_phone_number_2)->delete();
            }
        }

        
        
        // Handle image upload only if a new image is provided
        if ($this->image && $this->image instanceof \Illuminate\Http\UploadedFile) {
            $user->profile_image = $this->uploadImage();
        }

        // Handle video upload only if a new video is provided
        if ($this->verified_video && $this->verified_video instanceof \Illuminate\Http\UploadedFile) {
            $user->verified_video = $this->uploadVideo();
        }

        // Handle GST certificate upload only if a new certificate is provided
        if ($this->gst_certificate_image && $this->gst_certificate_image instanceof \Illuminate\Http\UploadedFile) {
            $user->gst_certificate_image = $this->uploadGSTCertificate();
        }

        $user->save();

        $this->storeAddress($user->id, 1, $this->billing_address, $this->billing_landmark, $this->billing_city, $this->billing_state, $this->billing_country, $this->billing_pin);

       

        session()->flash('success', 'Customer information updated successfully!');
        return redirect()->route('customers.index');
    }

    private function prepareUserData()
    {
        return [
            'customer_badge' => $this->badge_type,
            'prefix' => $this->prefix,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'employee_rank' => $this->employee_rank,
            'email' => $this->email,
            'dob'=>$this->dob,
            'country_code_phone' => $this->phone_code,
            'phone' => $this->phone,
            'country_code_whatsapp' => $this->phone_code,
            // 'whatsapp_no' => $this->whatsapp_no,
            'gst_number' => $this->gst_number,
            'credit_limit' => $this->credit_limit === '' ? 0 : $this->credit_limit,
            'credit_days' => $this->credit_days === '' ? 0 : $this->credit_days,
            'country_id' => $this->country_id,
            'country_code' => $this->country_code,
            'country_code_alt_1'  => $this->alt_phone_code_1,
            'alternative_phone_number_1' => $this->alternative_phone_number_1,
            'country_code_alt_2'  => $this->alt_phone_code_2,
            'alternative_phone_number_2' => $this->alternative_phone_number_2
        ];
    }

    private function storeAddress($userId, $addressType, $address, $landmark, $city, $state, $country, $zipCode)
    {
        UserAddress::updateOrCreate(
            ['user_id' => $userId, 'address_type' => $addressType],
            ['address' => $address, 'landmark' => $landmark, 'city' => $city, 'state' => $state, 'country' => $country, 'zip_code' => $zipCode]
        );
    }

    private function uploadImage()
    {
        return $this->handleFileUpload($this->image, 'profile_image');
    }

   

    private function uploadGSTCertificate()
    {
        return $this->handleFileUpload($this->gst_certificate_image, 'gst_certificate_image');
    }

    private function handleFileUpload($file, $folder)
    {
        if ($file && $file instanceof \Illuminate\Http\UploadedFile) {
            $timestamp = now()->timestamp;
            $fileName = $timestamp . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($folder, $fileName, 'public');
            return "storage/".$storedPath;
        }
        return null;
    }


    public function render()
    {
        return view('livewire.customer-edit');
    }
}
