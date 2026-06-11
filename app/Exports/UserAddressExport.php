<?php

namespace App\Exports;

use App\Models\UserAddress;
use Maatwebsite\Excel\Concerns\FromCollection;

class UserAddressExport implements FromCollection
{
    public function collection()
    {
        return UserAddress::select('user_id', 'address_type', 'address', 'landmark', 'city', 'state', 'country', 'zip_code')->get();
    }
}
