<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPhotoUpload extends Model
{
    protected $table = "customer_photo_uploads";
    protected $fillable = [
        'user_id', 'photo_path', 'consent_given', 'consent_given_at'
    ];

    
}
