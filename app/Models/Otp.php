<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Otp extends Model
{
    use HasFactory;
    protected $table = "otps";

    protected $fillable = [
        'phone',
        'email',
        // 'identifier',
        'otp',
        'expires_at',
        'employee_id',
    ];
}
