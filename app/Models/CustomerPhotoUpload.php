<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPhotoUpload extends Model
{
    protected $table = "customer_photo_uploads";
    protected $fillable = [
        'user_id', 'photo_path', 'consent_given', 'consent_given_at'
    ];
    
    public function marketingTypes()
    {
        return $this->belongsToMany(
            MarketingType::class,
            'customer_photo_marketing_types',
            'customer_photo_id',
            'marketing_type_id'
        );
    }
    
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
