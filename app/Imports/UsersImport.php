<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $row['email']], // Unique identifier
            [
                'emergency_contact_person' => $row['emergency_contact_person'] ?? null,
                'emergency_mobile' => $row['emergency_mobile'] ?? null,
                'emergency_whatsapp' => $row['emergency_whatsapp'] ?? null,
                'emergency_address' => $row['emergency_address'] ?? null,
                'branch_id' => $row['branch_id'] ?? null,
                'employee_id' => $row['employee_id'] ?? null,
                'country_id' => $row['country_id'] ?? null,
                'name' => $row['name'],
                'dob' => $row['dob'] ?? null,
                'user_type' => $row['user_type'] ?? null,
                'designation' => $row['designation'] ?? null,
                'company_name' => $row['company_name'] ?? null,
                'employee_rank' => $row['employee_rank'] ?? null,
                'phone' => $row['phone'] ?? null,
                'whatsapp_no' => $row['whatsapp_no'] ?? null,
                'aadhar_name' => $row['aadhar_name'] ?? null,
                'gst_number' => $row['gst_number'] ?? null,
                'gst_certificate_image' => $row['gst_certificate_image'] ?? null,
                'credit_limit' => $row['credit_limit'] ?? 0,
                'credit_days' => $row['credit_days'] ?? 0,
                'image' => $row['image'] ?? null,
                'passport_id_front' => $row['passport_id_front'] ?? null,
                'passport_id_back' => $row['passport_id_back'] ?? null,
                'passport_expiry_date' => $row['passport_expiry_date'] ?? null,
                'profile_image' => $row['profile_image'] ?? null,
                'verified_video' => $row['verified_video'] ?? null,
                'password' => bcrypt($row['password'] ?? 'defaultpassword'),
            ]
        );

        return $user;
    }
}
