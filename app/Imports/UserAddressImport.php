<?php

namespace App\Imports;

use App\Models\UserAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserAddressImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new UserAddress([
            'user_id' => $row['user_id'],
            'address_type' => $row['address_type'],
            'address' => $row['address'],
            'landmark' => $row['landmark'],
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => $row['country'],
            'zip_code' => $row['zip_code'],
        ]);
    }
}
