<?php

namespace App\Imports;

use App\Models\User;
// use App\Models\UserAddress;
// use App\Models\User;
use App\Models\UserAddress;
// use App\Models\Branch;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class UsersWithAddressesImport implements ToModel, WithHeadingRow
{
    
    
   

   public function model(array $row)
{
    try {
        $auth = Auth::guard('admin')->user();
        $dob = isset($row['dob']) ? Carbon::parse($row['dob'])->format('Y-m-d') : null;

        if (empty($row['phone'])) {
            session()->push('import_errors', [
                'row' => $row,
                'errors' => ['Phone is required for unique identification.'],
            ]);
            return null;
        }
         // Conditional customer_category
        if (isset($row['user_type']) && $row['user_type'] === 'Customer') {

            // If customer_category is missing or empty, you can default it
            if (empty($row['customer_category'])) {
                // You can decide default value: 'general' or 'premium'
                $row['customer_category'] = 'general';
            }

            // Optional: validate value
            if (!in_array($row['customer_category'], ['general', 'premium'])) {
                session()->push('import_errors', [
                    'row' => $row,
                    'errors' => ['Customer category must be either general or premium.'],
                ]);
                return null;
            }
        }

        $country = Country::where('country_code', $row['country_code_phone'])->first();
        $mobileLength = $country->mobile_length ?? 10;

        $countryCodeAlt1 = Country::where('country_code', $row['country_code_alternet_phone_one'])->first();
        $mobileLength1 = $countryCodeAlt1->mobile_length ?? 10;

        $countryCodeAlt2 = Country::where('country_code', $row['country_code_alternet_phone_two'])->first();
        $mobileLength2 = $countryCodeAlt2->mobile_length ?? 10;

        $validator = Validator::make($row, [
            'phone'              => "required|numeric|digits:$mobileLength",
            'alternet_phone_one' => "nullable|numeric|digits:$mobileLength1",
            'alternet_phone_two' => "nullable|numeric|digits:$mobileLength2",
            'email'              => ['nullable', 'email'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages(); // get all messages per field

           session()->push('import_errors', [
                'row' => $row,
                'errors' => $validator->errors()->getMessages(), // this is always an array
            ]);

            return null;
        }

        $user = User::updateOrCreate(
            ['phone' => $row['phone']],
            [
                'prefix' => $row['prefix'] ?? null,
                'name' => $row['customer_name'] ?? null,
                'dob' => $dob ?? null,
                'user_type' => strtolower(trim($row['user_type'])) == 'staff' ? 0 : 1,
                'customer_badge' => $row['customer_category'] ?? null,
                'company_name' => $row['company_name'] ?? null,
                'employee_rank' => $row['rank'] ?? null,
                'country_code_phone' => $row['country_code_phone'] ? '+' . $row['country_code_phone'] : null,
                'phone' => $row['phone'],
                'country_code_alt_1' => $row['country_code_alternet_phone_one'] ? '+' . $row['country_code_alternet_phone_one'] : null,
                'alternative_phone_number_1' => $row['alternet_phone_one'] ?? null,
                'country_code_alt_2' => $row['country_code_alternet_phone_two'] ? '+' . $row['country_code_alternet_phone_two'] : null,
                'alternative_phone_number_2' => $row['alternet_phone_two'] ?? null,
                'created_by' => $auth->id,
            ]
        );

        if (!empty($row['address'])) {
            UserAddress::updateOrCreate(
                ['user_id' => $user->id, 'address_type' => 1],
                [
                    'address' => $row['address'],
                    'landmark' => $row['landmark'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                    'country' => $row['country'] ?? null,
                    'zip_code' => $row['zip_code'] ?? null,
                ]
            );
        }

        \Log::info('Row imported successfully', ['row' => $row]);
        return $user;

    } catch (\Exception $e) {
        \Log::error('Exception during import', ['row' => $row, 'exception' => $e->getMessage()]);
        session()->push('import_errors', ['row' => $row, 'errors' => [$e->getMessage()]]);
        return null;
    }
}



    

     
}

