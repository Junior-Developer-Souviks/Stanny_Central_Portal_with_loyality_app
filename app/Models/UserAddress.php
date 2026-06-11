<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory; // Enable SoftDeletes for soft deleting functionality

    protected $table = 'user_address';

    protected $fillable = [
                            'user_id',
                            'address_type',
                            'address',
                            'landmark',
                            'city',
                            'state',
                            'country',
                            'zip_code'
                        ];

    // protected $dates = ['deleted_at'];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
    
}

            