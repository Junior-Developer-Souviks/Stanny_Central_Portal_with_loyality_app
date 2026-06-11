<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UserAddress;
// use App\Models\Branch;
// use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersAndAddressesExport implements FromCollection, WithHeadings
{
    

    public function collection()
    {
        // Fetch users along with their addresses using eager loading
        $users = User::with(['userAddress' => function ($query) {
                $query->where('address_type', 1); // Only Billing Address
            }])
            ->where('user_type', 1) // Adjust based on the requirement, assuming 1 means customers
            ->get();
    
        $data = $users->map(function ($user) {
            $userType = $user->user_type == 0 ? 'Staff' : 'Customer';
        
            // If no address exists, use empty values
            if ($user->userAddress->isEmpty()) {
                return [[
                    'User Type' => $userType,
                    'Prefix'   => $user->prefix,
                    'Customer Name' => $user->name,
                    'Company Name' => $user->company_name,
                    'Rank' => $user->employee_rank,
                    'Email' => $user->email,
                    'Country Code Phone' => $user->country_code_phone,
                    'Phone' => $user->phone,
                    'Country Code Alternet Phone One' => $user->country_code_alt_1,
                    'Alternet Phone One' => $user->alternative_phone_number_1,
                    'Country Code Alternet Phone Two' => $user->country_code_alt_2,
                    'Alternet Phone Two' => $user->alternative_phone_number_2,
                    'Country Code Whatsapp' => $user->country_code_whatsapp, // Added here
                    
                    'Whatsapp Number' => $user->whatsapp_no,
                    'DOB' => $user->dob,
                    'Address Type' => 'N/A', // No address
                    'Address' => '',
                    'Landmark' => '',
                    'City' => '',
                    'Country' => '',
                    'State' => '',
                    'Zip Code' => '',
                ]];
            }
        
            // If user has an address, return mapped data
            return $user->userAddress->map(function ($address) use ($user, $userType) {
                return [
                    'User Type' => $userType,
                    'Prefix'    => $user->prefix,
                    'Customer Name' => $user->name,
                    'Company Name' => $user->company_name,
                    'Rank' => $user->employee_rank,
                    'Email' => $user->email,
                    'Country Code Phone' => $user->country_code_phone,
                    'Phone' => $user->phone,
                    'Country Code Alternet Phone One' => $user->country_code_alt_1,
                    'Alternet Phone One' => $user->alternative_phone_number_1,
                    'Country Code Alternet Phone Two' => $user->country_code_alt_2,
                    'Alternet Phone Two' => $user->alternative_phone_number_2,
                    // 'Country Code Whatsapp' => $user->country_code_whatsapp, // Added here

                    // 'Whatsapp Number' => $user->whatsapp_no,
                    'DOB' => $user->dob,
                    // 'Address Type' => 'Billing Address',
                    'Address' => $address->address,
                    'Landmark' => $address->landmark,
                    'City' => $address->city,
                    'Country' => $address->country,
                    'State' => $address->state,
                    'Zip Code' => $address->zip_code,
                ];
            });
        })->flatten(1);
    
        // Return the collected data
        return $data;
    }
    

    public function headings(): array
    {
        return [
            'User Type',
            'Prefix',
            'Customer Name',
            'Company Name',
            'Rank',
            'Email',
            'Country Code Phone',
            'Phone',
            'Country Code Alternet Phone One',
            'Alternet Phone One',
            'Country Code Alternet Phone Two',
            'Alternet Phone Two',
            // 'Country Code Whatsapp', // Added here

            // 'Whatsapp Number',
            'DOB',
            // 'Address Type',
            'Address',
            'Landmark',
            'City',
            'Country',
            'State',
            'Zip Code',
        ];
    }
    
}

