<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\Designation;
use App\Models\UserBank;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Country;
use App\Models\Branch;
use App\Models\BusinessType;
use App\Models\UserWhatsapp;
use App\Models\ChangeLog;
use App\Helpers\Helper;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class StaffAdd extends Component
{
    use WithFileUploads;
    public $employee_id,$emp_code,$dob,$branch_id,$passport_issued_date,$designation, $prefix, $person_name, $prof_name, $surname, $email, $mobile, $aadhaar_number, $whatsapp_no,$is_wa_same,$user_id,$passport_no,$visa_no;
    public $image, $passport_id_front, $passport_id_back, $passport_expiry_date;
    public $account_holder_name, $bank_name, $branch_name, $account_no, $ifsc, $monthly_salary, $daily_salary, $travel_allowance;
    public $address, $landmark, $state, $city, $pincode, $country;
    public $designations = [];
    public $branchNames = [];
    public $countries;
    public $filteredCountries = []; 
    public $selectedCountryId;
    // public $searchTerm;
    public $Business_type;
    public $selectedBusinessType ;
    public $showRequiredFields  = false;
    public $emergency_contact_person,$emergency_mobile,$emergency_whatsapp,$emergency_address;
    public $countryCode;
    // public $country_code;
    public $mobileLength;
    public $alternative_phone_number_1;
    public $alternative_phone_number_2;
    public $password;
    public $selectedCountryPhone,$selectedCountryWhatsapp,$selectedCountryAlt1,$selectedCountryAlt2,$selectedCountryEmergencyContact,$selectedCountryEmergencyWhatsapp;
    public $mobileLengthPhone,$mobileLengthWhatsapp,$mobileLengthAlt1,$mobileLengthAlt2,$mobileLengthEmergencyContact,$mobileLengthEmergencyWhatsapp;
    public $isWhatsappPhone, $isWhatsappAlt1, $isWhatsappAlt2, $isWhatsappEmergency;
    public $team_lead;
    public $teamLeads =[];

    public function mount(){
        $this->designations = Designation::where('status',1)->orderBy('name', 'ASC')->where('id', '!=', 1)->get();
        $this->branchNames  = Branch::all();
        $this->countries = Country::where('status',1)->get();
        $this->Business_type = BusinessType::all();
        $this->selectedCountryId = null;
        $this->selectedBusinessType  = null;
        $this->emp_code = $this->generateEmpCode();
        $this->teamLeads = User::where('user_type',0)->get();
    }

    public function GetCountryDetails($mobileLength, $field){
        switch($field){
            case 'phone':
                $this->mobileLengthPhone  = $mobileLength;
                break;

            // case 'whatsapp':
            //     $this->mobileLengthWhatsapp = $mobileLength;
            //     break;

            case 'alt_phone_1':
                $this->mobileLengthAlt1 = $mobileLength;
                break;
            
            case 'alt_phone_2':
                $this->mobileLengthAlt2 = $mobileLength;
                break;

            case 'emergency_contact':
                $this->mobileLengthEmergencyContact = $mobileLength;
                break;

            // case 'emergency_whatsapp':
            //     $this->mobileLengthEmergencyWhatsapp = $mobileLength;
            //     break;
        }
    }

    public function generateEmpCode(){
        $lastUser = User::where('emp_code', 'LIKE', 'RI-%')
        ->orderBy('emp_code', 'DESC')
        ->first();

        if ($lastUser && preg_match('/RI-(\d+)/', $lastUser->emp_code, $matches)) {
            $nextNumber = str_pad($matches[1] + 1, 2, '0', STR_PAD_LEFT); 
        } else {
            $nextNumber = '01'; 
        }

        return 'RI-' . $nextNumber;
    }

    public function rules(){
        return[
            'branch_id'   => 'required',
            'designation' => 'required',
            'team_lead'  =>  'required',
            'emp_code' => 'required',
            'prefix' => 'required',
            'person_name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'dob' => 'nullable|date|before_or_equal:today',
            'prof_name' => 'required|string|max:255',
            'selectedBusinessType' => 'required|integer',
            // 'searchTerm' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
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
            //     'required', // At least VALIDATE_WHATSAPP digits
            //     'regex:/^\d{'. $this->mobileLengthWhatsapp .'}$/',
            // ],
            'passport_no' =>  'nullable|numeric',
            'visa_no' => 'nullable|numeric',
            'aadhaar_number' =>  'nullable|numeric',
            'image' => 'nullable|image|max:2048',
            'passport_id_front' => 'nullable|image|max:2048',
            'passport_id_back' => 'nullable|image|max:2048',
            'passport_expiry_date' => 'nullable',
            'passport_issued_date' => 'nullable',
            'account_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:20',
            'ifsc' => 'nullable|string|max:11',
            'monthly_salary' => 'required|numeric',
            'daily_salary' => 'nullable|numeric',
            'travel_allowance' => 'nullable|numeric',
            'address' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'state' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
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
        ];
    }

    public function message(){
        return[
            'branch_id.required' => 'Please select branch',
            'designation.required' => 'Designation is required.',
            'emp_code.required' => 'Employee code is required.',
            'person_name.required' => 'Person name is required.',
            'surname.required' => 'Surname is required.',
            // 'dob.required' => 'Date of birth is required.',
            'prof_name.required' => 'Professional name is required.',
            'selectedBusinessType.required' => 'Business type selection is required.',
            // 'searchTerm.required' => 'Please search a country name',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Mobile number must be exactly '. $this->mobileLengthPhone .' digits',
            'mobile.unique' => 'This mobile number is already registered.',
            
            // 'whatsapp_no.required' => 'WhatsApp number is required.',
            // 'whatsapp_no.regex' => 'WhatsApp number must be exactly '. $this->mobileLengthWhatsapp .' digits',
            
            // 'passport_no.required' => 'Passport number is required.',
            // 'visa_no.required' => 'Visa number is required.',
            // 'aadhaar_number.required' => 'Aadhaar number is required.',
            
            'image.image' => 'Only image files are allowed.',
            'image.max' => 'Image size must be less than 2MB.',
            
            'monthly_salary.required' => 'Monthly salary is required.',
            'monthly_salary.numeric' => 'Enter a valid salary amount.',
            
           
            'alternative_phone_number_1.regex' => 'Alternative mobile number 1 must be exactly'. $this->mobileLengthAlt1 .' digits',
            'alternative_phone_number_2.regex' => 'Alternative mobile number 2 must be exactly'. $this->mobileLengthAlt2 .' digits',
            'emergency_mobile.regex' => 'Emergency mobile number must be exactly'. $this->mobileLengthEmergencyContact .' digits',
            // 'emergency_whatsapp.regex' => 'Emergency whatsapp number must be exactly'. $this->mobileLengthEmergencyWhatsapp .' digits',
            'emergency_contact_person.string' => 'Emergency contact person name must be text',
            'emergency_address.string' => 'Emergency address must be text',
        ];
    }
  


    public function save(){
        // dd($this->all());
        $this->validate();
       DB::beginTransaction();

       try { 

        // Check and upload the images only if they are provided
            $imagePath = $this->image ? Helper::uploadImage($this->image, 'staff2') : null;
            $passportIdFrontPath = $this->passport_id_front ? Helper::uploadImage($this->passport_id_front, 'staff') : null;
            $passportIdBackPath = $this->passport_id_back ? Helper::uploadImage($this->passport_id_back, 'staff') : null;

            // Now, you can handle these paths accordingly (e.g., store them in the database)


            // 1. Save the data into the users table
            $user = User::create([
                // 'employee_id' => $formattedEmployeeId,
                'emp_code' => $this->emp_code,
                'business_type' => $this->selectedBusinessType,
                'surname' => $this->surname,
                'prof_name'=> $this->prof_name,
                'dob'=> $this->dob,
                'branch_id' => $this->branch_id,
                'country_id'=> $this->selectedCountryId,
                'user_type' => 0, //for Staff
                'designation' => $this->designation ?? "",
                'parent_id'  =>  $this->team_lead, 
                'prefix'    => $this->prefix,
                'name' => ucwords($this->person_name) ?? "",
                'email' => $this->email ?? "",
                // 'country_code' => $this->country_code ?? '',
                'country_code_phone' => $this->selectedCountryPhone,
                'phone' => $this->mobile ?? "",
                'aadhar_name' => $this->aadhaar_number ?? "",
                // 'country_code_whatsapp' => $this->selectedCountryWhatsapp,
                // 'whatsapp_no' => $this->whatsapp_no ?? "",
                'image' =>  $imagePath ?? "",
                'passport_id_front' =>  $passportIdFrontPath ?? "",
                'passport_id_back' => $passportIdBackPath ?? "",
                'passport_expiry_date' => !empty($this->passport_expiry_date) ? $this->passport_expiry_date : null,
                'passport_issued_date' => !empty($this->passport_issued_date) ? $this->passport_issued_date : null,
                'passport_no' => $this->passport_no,
                'visa_no' => $this->visa_no,
                'password'=> Hash::make($this->password),
                'emergency_contact_person' => $this->emergency_contact_person ?? "",
                'country_code_emergency_mobile' => $this->selectedCountryEmergencyContact,
                'emergency_mobile' => $this->emergency_mobile ?? "",
                // 'country_code_emergency_whatsapp' => $this->selectedCountryEmergencyWhatsapp,
                // 'emergency_whatsapp' => $this->emergency_whatsapp ?? "",
                'emergency_address' => $this->emergency_address ?? "",
                'country_code_alt_1' => $this->selectedCountryAlt1,
                'alternative_phone_number_1' => $this->alternative_phone_number_1 ?? "",
                'country_code_alt_2' => $this->selectedCountryAlt2,
                'alternative_phone_number_2' => $this->alternative_phone_number_2 ?? ""
            ]);

            if($this->isWhatsappPhone){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->mobile)
                                                ->where('user_id','!=', $user->id)
                                                ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    [ 'whatsapp_number' => $this->mobile],
                    [ 'user_id' => $user->id, 'country_code' => $this->selectedCountryPhone, 'created_at' => now(),'updated_at' => now()],
                );
             }
            }

            if($this->isWhatsappAlt1){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_1)
                                                    ->where('user_id','!=', $user->id)
                                                    ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate(
                    [ 'whatsapp_number' => $this->alternative_phone_number_1], // Find condition
                    ['user_id' => $user->id, 'country_code' => $this->selectedCountryPhone, 'created_at' => now(), 'updated_at' => now()]
                ); 
             }
            }

            if($this->isWhatsappAlt2){
             $existingRecord = UserWhatsapp::where('whatsapp_number', $this->alternative_phone_number_2)
                                                    ->where('user_id','!=', $user->id)
                                                    ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate([
                    'whatsapp_number' => $this->alternative_phone_number_2],
                    [
                    'user_id' => $user->id,
                    'country_code' => $this->selectedCountryAlt2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

            if($this->isWhatsappEmergency){
                $existingRecord = UserWhatsapp::where('whatsapp_number', $this->emergency_mobile)
                                                    ->where('user_id','!=', $user->id)
                                                    ->exists();
            if(!$existingRecord){
                UserWhatsapp::updateOrCreate([
                    'user_id' => $user->id,
                    'whatsapp_number' => $this->emergency_mobile],
                    [
                    'country_code' => $this->selectedCountryEmergencyContact,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
            

            // 2. Save the data into the user_banks table
           $bank = UserBank::create([
                'user_id' => $user->id,
                'account_holder_name' => $this->account_holder_name ?? "",
                'bank_name' => $this->bank_name ?? "",
                'branch_name' => $this->branch_name ?? "",
                'bank_account_no' => $this->account_no ?? "",
                'ifsc' => $this->ifsc ?? "",
                'monthly_salary' => is_numeric($this->monthly_salary) ? $this->monthly_salary : null,
                'bonus' => is_numeric($this->daily_salary) ?  $this->daily_salary : null,
                'past_salaries' => is_numeric($this->travel_allowance) ? $this->travel_allowance : null,
            ]);

            // 3. Save the data into the user_address table
          $address = UserAddress::create([
                'user_id' => $user->id,
                'address_type' => 1, //for Staff
                'address' => $this->address ?? "",
                'landmark' => $this->landmark ?? "",
                'state' => $this->state ?? "",
                'city' => $this->city ?? "",
                'zip_code' => $this->pincode ?? "",
                'country' => $this->country ?? "",
            ]);

            // prepare all the data for changelog
            $allData = [
                'user' => $user->toArray(),
                'bank' => $bank->toArray(),
                'address' => $address->toArray(),
                'form_data' => [
                    'employee_id' => $this->employee_id,
                    'emp_code' => $this->emp_code,
                    'dob' => $this->dob,
                    'branch_id' => $this->branch_id,
                    'passport_issued_date' => $this->passport_issued_date,
                    'designation' => $this->designation,
                    'prefix' => $this->prefix,
                    'person_name' => $this->person_name.' '.$this->surname,
                    'prof_name' => $this->prof_name,
                    'email' => $this->email,
                    'mobile' => $this->mobile,
                    'aadhaar_number' => $this->aadhaar_number,
                    'whatsapp_no' => $this->whatsapp_no,
                    'is_wa_same' => $this->is_wa_same,
                    'user_id' => $this->user_id,
                    'passport_no' => $this->passport_no,
                    'visa_no' => $this->visa_no,
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
                    'emergency_contact_person' => $this->emergency_contact_person,
                    'emergency_mobile' => $this->emergency_mobile,
                    'emergency_whatsapp' => $this->emergency_whatsapp,
                    'emergency_address' => $this->emergency_address,
                    'selectedCountryId' => $this->selectedCountryId,
                    'selectedBusinessType' => $this->selectedBusinessType,
                    'alternative_phone_number_1' => $this->alternative_phone_number_1,
                    'alternative_phone_number_2' => $this->alternative_phone_number_2,
                    'selectedCountryPhone' => $this->selectedCountryPhone,
                    'selectedCountryWhatsapp' => $this->selectedCountryWhatsapp,
                    'selectedCountryAlt1' => $this->selectedCountryAlt1,
                    'selectedCountryAlt2' => $this->selectedCountryAlt2,
                    'selectedCountryEmergencyContact' => $this->selectedCountryEmergencyContact,
                    'selectedCountryEmergencyWhatsapp' => $this->selectedCountryEmergencyWhatsapp,
                    'isWhatsappPhone' => $this->isWhatsappPhone,
                    'isWhatsappAlt1' => $this->isWhatsappAlt1,
                    'isWhatsappAlt2' => $this->isWhatsappAlt2,
                    'isWhatsappEmergency' => $this->isWhatsappEmergency,
                    'team_lead' => $this->team_lead,
                    'image_path' => $imagePath,
                    'passport_id_front_path' => $passportIdFrontPath,
                    'passport_id_back_path' => $passportIdBackPath,
                    'passport_expiry_date' => $this->passport_expiry_date,
                    'password' => $this->password 
                ]
            ];


           
             $teamLead = User::find($this->team_lead);
              $allData['team_lead_name'] = $teamLead ? $teamLead->name : 'N/A';
            // 4. Save the data into changelog table
            ChangeLog::create([
                'done_by' => auth()->guard('admin')->user()->id,
                'purpose' => 'staff_add',
                'data_details' => json_encode($allData)
            ]);

            // Commit the transaction if everything is successful
            DB::commit();

            session()->flash('message', 'Staff information saved successfully!');
            return redirect()->route('staff.index');
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Handle the exception (e.g., log the error and show an error message)
            session()->flash('error', 'An error occurred while saving staff information: ' . $e->getMessage());
            dd($e->getMessage());
            return back()->withInput();
        }
    }



    public function render()
    {
        $this->dispatch('error_message');
        return view('livewire.staff.staff-add');
    }
}
