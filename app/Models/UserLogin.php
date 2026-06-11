<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $table = "user_logins";
    protected $fillable = [
       'user_id', 'country_code','email', 'mobile', 'is_verified', 'otp', 'mpin', 'device_id', 'created_at', 'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
