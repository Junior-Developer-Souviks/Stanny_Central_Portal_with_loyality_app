<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPhoto extends Model
{
    protected $table = "customer_photos";
    protected $fillable = [
            'user_id', 'image', 'consent_given', 'consent_text', 'status', 'is_used_marketing'
        ];
}
