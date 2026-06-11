<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    use HasFactory;
    protected $table = "user_banks";
    protected $fillable = [
        'user_id', 
        'account_holder_name',
        'bank_name',
        'branch_name',
        'bank_account_no', // Adjust the column name based on your table structure
        'ifsc',
        'monthly_salary',
        'bonus',
        'past_salaries',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
