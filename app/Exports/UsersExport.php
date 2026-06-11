<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::select([
            'name', 'email', 'phone', 'whatsapp_no', 'designation', 'company_name',
            'credit_limit', 'credit_days', 'gst_number', 'employee_id',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Phone', 'WhatsApp No', 'Designation', 'Company Name',
            'Credit Limit', 'Credit Days', 'GST Number', 'Employee ID', 
        ];
    }
}
