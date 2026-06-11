<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\User;
use App\Models\Country;
use App\Models\Designation;
use App\Models\BusinessType;
use App\Models\Branch;
use App\Models\UserWhatsapp;
use App\Models\ChangeLog;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;



class StaffUpdate extends Component
{
    use WithFileUploads;

    public $staff,$branchNames, $designation, $prefix, $person_name, $surname, $emp_code, $prof_name, $email, $mobile, $aadhaar_number, $whatsapp_no , $passport_no , $dob, $passport_issued_date , $visa_no, $emergency_contact_person, $emergency_whatsapp ,$emergency_mobile , $emergency_address, $same_as_contact , $alternative_phone_number_1, $alternative_phone_number_2;
    public $image, $passport_id_front, $passport_id_back , $passport_expiry_date;
    public $account_holder_name, $bank_name, $branch_name, $account_no, $ifsc;
    public $monthly_salary, $daily_salary, $travel_allowance;
    public $address, $landmark, $state, $city, $pincode, $country;
    public $staff_id,$designations,$user_id;  // Variable to hold the staff id
    // public $Selectcountry;
    public $selectedCountryId;
    public $Business_type;
    public $selectedBusinessType;
    public $selectedBranchId;
    // public $showRequiredFields = false;
    public $countries;
    // public $searchTerm;
    public $selectedCountryPhone,$selectedCountryWhatsapp,$selectedCountryAlt1,$selectedCountryAlt2,$selectedCountryEmergencyContact,$selectedCountryEmergencyWhatsapp;

    public $mobileLengthPhone,$mobileLengthWhatsapp,$mobileLengthAlt1,$mobileLengthAlt2,$mobileLengthEmergencyContact,$mobileLengthEmergencyWhatsapp;
    
    public $isWhatsappPhone, $isWhatsappAlt1, $isWhatsappAlt2, $isWhatsappEmergency;
    public $team_lead;
    public $teamLeads = [];


    public function mount($staff_id){
        $this->staff = User::with(['branch','businessType','bank','address','designationDetails','userWhatsapp'])->find($staff_id);
        $this->countries = Country::where('status',1)->get();
        $this->Business_type = BusinessType::all();
        $this->branchNames = Branch::all();
        $this->teamLeads = User::where('user_type',0)->where('id', '!=', $staff_id)->get();
        // dd( $this->staff->designationDetails->id);
        $this->designations = Designation::latest()->get();
         // If staff exists, assign the data to the public variables
         if ($this->staff) {
            // $this->selectedCountryId = $this->staff->country_id;
            $this->selectedBusinessType = $this->staff->business_type;
            $this->selectedBranchId = $this->staff->branch_id;
            $this->designation = $this->staff->designationDetails->id;
            $this->team_lead = $this->staff->parent_id;
            $this->prefix = $this->staff->prefix;
            $this->person_name = $this->staff->name;
            $this->surname = $this->staff->surname;
            $this->emp_code = $this->staff->emp_code;
            $this->prof_name = $this->staff->prof_name;
            $this->email = $this->staff->email;
            $this->mobile = $this->staff->phone;
            $this->aadhaar_number = $this->staff->aadhar_name;
            // $this->whatsapp_no = $this->staff->whatsapp_no;
           
            $this->image = $this->staff->image;
            $this->passport_id_front = $this->staff->passport_id_front;
            $this->passport_id_back = $this->staff->passport_id_back;
            $this->passport_expiry_date = $this->staff->passport_expiry_date;
            $this->passport_no = $this->staff->passport_no;
            $this->dob = $this->staff->dob;
            $this->passport_issued_date = $this->staff->passport_issued_date;
            $this->visa_no = $this->staff->visa_no;
            $this->alternative_phone_number_1 = $this->staff->alternative_phone_number_1;
            $this->alternative_phone_number_2 = $this->staff->alternative_phone_number_2;
            $this->emergency_contact_person  = $this->staff->emergency_contact_person;
            $this->emergency_mobile  = $this->staff->emergency_mobile;
            $this->emergency_whatsapp  = $this->staff->emergency_whatsapp;
            $this->emergency_address  = $this->staff->emergency_address;
            
            // getting the country code
            $this->selectedCountryPhone = $this->staff->country_code_phone;
            $this->selectedCountryWhatsapp = $this->staff->country_code_whatsapp;
            $this->selectedCountryAlt1 = $this->staff->country_code_alt_1;
            $this->selectedCountryAlt2 = $this->staff->country_code_alt_2;
            $this->selectedCountryEmergencyContact = $this->staff->country_code_emergency_mobile;
            $this->selectedCountryEmergencyWhatsapp = $this->staff->country_code_emergency_whatsapp;

            // getting the mobile length by the selected country code
            $this->mobileLengthPhone = Country::where('country_code',$this->selectedCountryPhone)->value('mobile_length') ?? '';
            $this->mobileLengthWhatsapp = Country::where('country_code',$this->selectedCountryWhatsapp)->value('mobile_length') ?? '';
            $this->mobileLengthAlt1 = Country::where('country_code',$this->selectedCountryAlt1)->value('mobile_length') ?? '';
            $this->mobileLengthAlt2 = Country::where('country_code',$this->selectedCountryAlt2)->value('mobile_length') ?? '';
            $this->mobileLengthEmergencyContact = Country::where('country_code',$this->selectedCountryEmergencyContact)->value('mobile_length') ?? '';
            $this->mobileLengthEmergencyWhatsapp = Country::where('country_code',$this->selectedCountryEmergencyWhatsapp)->value('mobile_length') ?? '';

            // checkbox pre-selected if the number is also a whatsapp number
            $this->isWhatsappPhone = UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->mobile)->exists();
            $this->isWhatsappAlt1 = UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->alternative_phone_number_1)->exists();
            $this->isWhatsappAlt2 = UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->alternative_phone_number_2)->exists();
            $this->isWhatsappEmergency = UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->emergency_mobile)->exists();

            // Bank Information
            $this->account_holder_name = $this->staff->bank->account_holder_name;
            $this->bank_name = $this->staff->bank->bank_name;
            $this->branch_name = $this->staff->bank->branch_name;
            $this->account_no = $this->staff->bank->bank_account_no;
            $this->ifsc = $this->staff->bank->ifsc;
            $this->monthly_salary = $this->staff->bank->monthly_salary;
            $this->daily_salary = $this->staff->bank->bonus;
            $this->travel_allowance = $this->staff->bank->past_salaries;
              // Address Information
            $this->address = $this->staff->address->address;
            $this->landmark = $this->staff->address->landmark;
            $this->state = $this->staff->address->state;
            $this->city = $this->staff->address->city;
            $this->pincode = $this->staff->address->zip_code;
            $this->country = $this->staff->address->country;
        }


    }

    public function GetCountryDetails($mobileLength, $field){
        switch($field){
            case 'phone':
                $this->mobileLengthPhone  = $mobileLength;
                break;

            case 'whatsapp':
                $this->mobileLengthWhatsapp = $mobileLength;
                break;

            case 'alt_phone_1':
                $this->mobileLengthAlt1 = $mobileLength;
                break;
            
            case 'alt_phone_2':
                $this->mobileLengthAlt2 = $mobileLength;
                break;

            case 'emergency_contact':
                $this->mobileLengthEmergencyContact = $mobileLength;
                break;

            case 'emergency_whatsapp':
                $this->mobileLengthEmergencyWhatsapp = $mobileLength;
                break;
                
        }
    }

    public function rules(){
        return [
            'designation' => 'required',
            'team_lead' => [
            'required',
            'exists:users,id',
            function ($attribute, $value, $fail) {
                    if ($value == $this->staff->id) {
                        $fail('You cannot set yourself as your own parent.');
                    }
                },
            ],
            'prof_name' => 'required',
            'surname'  => 'required',
            'dob'  => 'nullable',
            'person_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'alternative_phone_number_1' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt1 .'}$/',
            ],  
            'alternative_phone_number_2' => [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthAlt2 .'}$/',
            ],
            'mobile' => [
                'required',
                'regex:/^\d{'. $this->mobileLengthPhone .'}$/',
            ],
            // 'whatsapp_no' => [
            //     'required',
            //     'regex:/^\d{'. $this->mobileLengthWhatsapp .'}$/',
            // ],
            'emergency_mobile'=> [
                'nullable',
                'regex:/^\d{'. $this->mobileLengthEmergencyContact .'}$/', // At least VALIDATE_MOBILE digits
            ],
            // 'emergency_whatsapp'=>[
            //     'nullable',
            //     'regex:/^\d{'. $this->mobileLengthEmergencyWhatsapp .'}$/',
            // ],
            'emergency_contact_person' => 'nullable|string',
            'emergency_address' => 'nullable|string',
            'aadhaar_number' =>  'nullable|numeric',
            'passport_no'=>   'nullable|numeric',
            'visa_no'=>   'nullable|numeric',
            'image' => 'nullable|max:2048',
            'passport_id_front' => 'nullable|max:2048',
            'passport_id_back' => 'nullable|max:2048',
            'passport_expiry_date' => 'nullable',
            'account_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|numeric',
            'ifsc' => 'nullable|string',
            'monthly_salary' => 'required|numeric',
            'daily_salary' => 'nullable|numeric',
            'travel_allowance' => 'nullable|numeric',
            'address' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'state' => 'nullable:string',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|numeric',
            'country' => 'nullable|string|max:255',
        ];
    }

    public function messages(){
        return [
            'designation.required' => 'The designation field is required.',
            'prof_name.required' => 'The professional name is required.',
            'surname.required' => 'The surname field is required.',
            // 'dob.required' => 'The date of birth is required.',
            'person_name.required' => 'The person name is required.',
            'person_name.max' => 'The person name may not be greater than 255 characters.',
            'email.email' => 'Please enter a valid email address.',
            'mobile.required' => 'The mobile number is required.',
            'mobile.regex' => 'The mobile number must be exactly ' . $this->mobileLengthPhone . ' digits.',
            // 'whatsapp_no.required' => 'The WhatsApp number is required.',
            // 'whatsapp_no.regex' => 'The WhatsApp number must be exactly ' . $this->mobileLengthWhatsapp . ' digits.',
            'alternative_phone_number_1.regex' => 'The alternative phone number must be exactly ' . $this->mobileLengthAlt1 . ' digits.',
            'alternative_phone_number_2.regex' => 'The alternative phone number must be exactly ' . $this->mobileLengthAlt2 . ' digits.',
            'emergency_mobile.regex' => 'The emergency mobile number must be exactly ' . $this->mobileLengthEmergencyContact . ' digits.',
            // 'emergency_whatsapp.regex' => 'The emergency WhatsApp number must be exactly ' . $this->mobileLengthEmergencyWhatsapp . ' digits.',
            'aadhaar_number.numeric' => 'The Aadhaar number must be a valid number.',
            'passport_no.numeric' => 'The passport number must be a valid number.',
            'visa_no.numeric' => 'The visa number must be a valid number.',
            'image.max' => 'The image must not exceed 2MB.',
            'passport_id_front.max' => 'The passport front image must not exceed 2MB.',
            'passport_id_back.max' => 'The passport back image must not exceed 2MB.',
            'monthly_salary.required' => 'The monthly salary field is required.',
            'monthly_salary.numeric' => 'The monthly salary must be a numeric value.',
            'daily_salary.numeric' => 'The daily salary must be a numeric value.',
            'travel_allowance.numeric' => 'The travel allowance must be a numeric value.',
           
           
        ];
    }

    

    public function update(){
        $this->validate();

        try {
            // Capture OLD data before update
            $oldStaffData = $this->staff->toArray();
            $oldBankData = $this->staff->bank ? $this->staff->bank->toArray() : [];
            $oldAddressData = $this->staff->address ? $this->staff->address->toArray() : [];
            $oldWhatsappNumbers = $this->staff->userWhatsapp ? $this->staff->userWhatsapp->toArray() : [];
               // Prepare old data for changelog
            $oldData = [
                'old_staff' => $oldStaffData,
                'old_bank' => $oldBankData,
                'old_address' => $oldAddressData,
                'old_whatsapp_numbers' => $oldWhatsappNumbers,
                'form_data_before_update' => [
                    'branch_id' => $this->staff->branch_id,
                    'designation' => $this->staff->designation,
                    'team_lead' => $this->staff->parent_id,
                    'prefix' => $this->staff->prefix,
                    'person_name' => $this->staff->name.' '.$this->staff->surname,
                    'emp_code' => $this->staff->emp_code,
                    'prof_name' => $this->staff->prof_name,
                    'email' => $this->staff->email,
                    'mobile' => $this->staff->phone,
                    'aadhaar_number' => $this->staff->aadhar_name,
                    'passport_no' => $this->staff->passport_no,
                    'dob' => $this->staff->dob,
                    'passport_issued_date' => $this->staff->passport_issued_date,
                    'visa_no' => $this->staff->visa_no,
                    'emergency_contact_person' => $this->staff->emergency_contact_person,
                    'emergency_mobile' => $this->staff->emergency_mobile,
                    'emergency_whatsapp' => $this->staff->emergency_whatsapp,
                    'emergency_address' => $this->staff->emergency_address,
                    'alternative_phone_number_1' => $this->staff->alternative_phone_number_1,
                    'alternative_phone_number_2' => $this->staff->alternative_phone_number_2,
                    'selectedCountryPhone' => $this->staff->country_code_phone,
                    'selectedCountryWhatsapp' => $this->staff->country_code_whatsapp,
                    'selectedCountryAlt1' => $this->staff->country_code_alt_1,
                    'selectedCountryAlt2' => $this->staff->country_code_alt_2,
                    'selectedCountryEmergencyContact' => $this->staff->country_code_emergency_mobile,
                    'selectedCountryEmergencyWhatsapp' => $this->staff->country_code_emergency_whatsapp,
                    'image' => $this->staff->image,
                    'passport_id_front' => $this->staff->passport_id_front,
                    'passport_id_back' => $this->staff->passport_id_back,
                    'passport_expiry_date' => $this->staff->passport_expiry_date,
                    'account_holder_name' => $this->account_holder_name,
                    'bank_name' => $this->bank_name,
                    'branch_name' => $this->branch_name,
                    'account_no' => $this->account_no,
                    'ifsc' => $this->ifsc,
                    'monthly_salary' => $this->monthly_salary,
                    'daily_salary' => $this->daily_salary,
                    'travel_allowance' => $this->travel_allowance,
                    'address' => $this->address,
                    'landmark' => $this->landmark,
                    'state' => $this->state,
                    'city' => $this->city,
                    'pincode' => $this->pincode,
                    'country' => $this->country,
                    'selectedCountryId' => $this->selectedCountryId,
                    'selectedBusinessType' => $this->selectedBusinessType,
                    'isWhatsappPhone' => $this->isWhatsappPhone,
                    'isWhatsappAlt1' => $this->isWhatsappAlt1,
                    'isWhatsappAlt2' => $this->isWhatsappAlt2,
                    'isWhatsappEmergency' => $this->isWhatsappEmergency,
                ]
            ];

             $teamLead = User::find($this->team_lead);
            $oldData['team_lead_name'] = $teamLead ? $teamLead->name : 'N/A';

            // Store the files
            $imagePath = $this->image && $this->image instanceof \Illuminate\Http\UploadedFile ? $this->image->store('images', 'public') : $this->staff->image;
            $passportIdFrontPath = $this->passport_id_front && $this->passport_id_front instanceof \Illuminate\Http\UploadedFile ? $this->passport_id_front->store('user_ids', 'public') : $this->staff->passport_id_front;
            $passportIdBackPath = $this->passport_id_back && $this->passport_id_back instanceof \Illuminate\Http\UploadedFile ? $this->passport_id_back->store('user_ids', 'public') : $this->staff->passport_id_back;
            
            // Update the staff record
            $this->staff->update([
                'country_id'=> $this->selectedCountryId,
                'branch_id'=> $this->selectedBranchId,
                'designation'=> $this->designation,
                'parent_id'  => $this->team_lead,
                'prefix'   => $this->prefix,
                'name' => $this->person_name ?? '',
                'emp_code' => $this->emp_code ?? '',
                'surname' => $this->surname ?? '',
                'prof_name' => $this->prof_name ?? '',
                'dob' => $this->dob ?: null,
                'business_type' => $this->selectedBusinessType ?? '',
                'email' => $this->email ?? '',
                'country_code_phone' => $this->selectedCountryPhone,
                'phone' => $this->mobile ?? '',
                'aadhar_name' => $this->aadhaar_number ?? '',
                // 'country_code_whatsapp' => $this->selectedCountryWhatsapp,
                // 'whatsapp_no' => $this->whatsapp_no ?? '',
                'image' => $imagePath ?? '',
                'passport_id_front' => $passportIdFrontPath ?? '',
                'passport_id_back' => $passportIdBackPath ?? '',
                'passport_no' => $this->passport_no ?? '',
                'visa_no' => $this->visa_no ?? '',
                'passport_expiry_date' => !empty($this->passport_expiry_date) ? $this->passport_expiry_date : null,
                'passport_issued_date' => !empty($this->passport_issued_date) ? $this->passport_issued_date : null,
                'emergency_contact_person'=> $this->emergency_contact_person,
                'country_code_emergency_mobile' => $this->selectedCountryEmergencyContact,
                'emergency_mobile' => $this->emergency_mobile,
                // 'country_code_emergency_whatsapp' => $this->selectedCountryEmergencyWhatsapp,
                // 'emergency_whatsapp' => $this->emergency_whatsapp,
                'emergency_address' => $this->emergency_address,
                // 'country_code' => $this->country_code,
                'country_code_alt_1' => $this->selectedCountryAlt1,
                'alternative_phone_number_1' => $this->alternative_phone_number_1,
                'country_code_alt_2' => $this->selectedCountryAlt2,
                'alternative_phone_number_2' => $this->alternative_phone_number_2,
                
            ]);

            if($this->isWhatsappPhone){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->mobile)
                                                    ->where('user_id','!=', $this->staff->id)
                                                    ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $this->staff->id, 'whatsapp_number' => $this->mobile],
                    ['country_code' => $this->selectedCountryPhone, 'updated_at' => now()]
                ); 
            } 
            }else{
                if(!empty($this->mobile)){
                    UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->mobile)->delete();
                }
            }


            if($this->isWhatsappAlt1){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_1)
                                                        ->where('user_id','!=', $this->staff->id)
                                                        ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $this->staff->id, 'whatsapp_number' => $this->alternative_phone_number_1],
                    ['country_code' => $this->selectedCountryAlt1, 'updated_at' => now()]
                ); 
            }
            }else{
                if(!empty($this->alternative_phone_number_1)){
                    UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->alternative_phone_number_1)->delete();
                }
            }

            if($this->isWhatsappAlt2){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_2)
                                                        ->where('user_id','!=', $this->staff->id)
                                                        ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $this->staff->id, 'whatsapp_number' => $this->alternative_phone_number_2],
                    ['country_code' => $this->selectedCountryAlt2, 'updated_at' => now()]
                ); 
            }
            }else{
                if(!empty($this->alternative_phone_number_2)){
                    UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->alternative_phone_number_2)->delete();
                }
            }

            if($this->isWhatsappEmergency){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->emergency_mobile)
                                                        ->where('user_id','!=', $this->staff->id)
                                                        ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    ['user_id' => $this->staff->id, 'whatsapp_number' => $this->emergency_mobile],
                    ['country_code' => $this->selectedCountryEmergencyContact, 'updated_at' => now()]
                ); 
            }
            }else{
                if(!empty($this->emergency_mobile)){
                    UserWhatsapp::where('user_id',$this->staff->id)->where('whatsapp_number',$this->emergency_mobile)->delete();
                }
            }




            // Update bank details
            if ($this->staff->bank) {
                $this->staff->bank->update([
                    'account_holder_name' => $this->account_holder_name ?? '',
                    'bank_name' => $this->bank_name ?? '',
                    'branch_name' => $this->branch_name ?? '',
                    'bank_account_no' => $this->account_no ?? '',
                    'ifsc' => $this->ifsc ?? '',
                    'monthly_salary' => is_numeric($this->monthly_salary) ? $this->monthly_salary : null,
                    'daily_salary' => is_numeric($this->daily_salary) ?  $this->daily_salary : null,
                    'travelling_allowance' => is_numeric($this->travel_allowance) ? $this->travel_allowance : null,
                ]);
            } else {
                // If no bank record, create a new one
                $this->staff->bank()->create([
                    'account_holder_name' => $this->account_holder_name ?? '',
                    'bank_name' => $this->bank_name ?? '',
                    'branch_name' => $this->branch_name ?? '',
                    'bank_account_no' => $this->account_no ?? '',
                    'ifsc' => $this->ifsc ?? '',
                    'monthly_salary' => is_numeric($this->monthly_salary) ? $this->monthly_salary : null,
                    'bonus' => is_numeric($this->daily_salary) ?  $this->daily_salary : null,
                    'past_salaries' => is_numeric($this->travel_allowance) ? $this->travel_allowance : null,
                ]);
            }
            // Update address details
            if ($this->staff->address) {
                $this->staff->address->update([
                    'address' => $this->address ?? '',
                    'address_type' => 1,
                    'landmark' => $this->landmark ?? '',
                    'state' => $this->state ?? '',
                    'city' => $this->city ?? '',
                    'zip_code' => $this->pincode ?? '',
                    'country' => $this->country ?? '',
                ]);
            } else {
                // If no address record, create a new one
                $this->staff->address()->create([
                    'address' => $this->address ?? '',
                    'address_type' => 1,
                    'landmark' => $this->landmark ?? '',
                    'state' => $this->state ?? '',
                    'city' => $this->city ?? '',
                    'zip_code' => $this->pincode ?? '',
                    'country' => $this->country ?? '',
                ]);
            }

            // Create changelog with OLD data
            ChangeLog::create([
                'done_by' => auth()->guard('admin')->user()->id,
                'purpose' => 'staff_update',
                'data_details' => json_encode($oldData)
            ]);

            session()->flash('message', 'Staff updated successfully');
            return redirect()->route('staff.index');
        } catch (\Exception $e) {
            // If any error occurs, catch it and flash an error message
            session()->flash('error', 'There was an error while updating the staff: ' . $e->getMessage());
            dd($e->getMessage());
            // Optionally log the error for debugging purposes
            \Log::error('Staff update failed: ' . $e->getMessage());

            // Redirect back to the edit page or wherever necessary
            return back();
        }
    }

    // public function SameAsMobile(){
    //     if($this->is_wa_same == 0){
    //         $this->whatsapp_no = $this->mobile;
    //         $this->is_wa_same =1;
    //     }else{
    //         $this->whatsapp_no = '';
    //         $this->is_wa_same = 0;
    //     }
    // }

    public function sameAsContact(){
        if($this->same_as_contact){
            $this->emergency_whatsapp = $this->emergency_mobile;
            $this->same_as_contact = 1;
        }else{
            $this->emergency_whatsapp = '';
            $this->same_as_contact = 0;
        }
    }

    public function render()
    {
        $this->dispatch('error_message');
        return view('livewire.staff.staff-update',['designations'=>$this->designations]);
    }
}
