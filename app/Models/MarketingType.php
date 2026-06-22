<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingType extends Model
{
    protected $table = "marketing_types";
    protected $fillable = [
           'name','status'
        ];
        
    public function photos()
    {
        return $this->belongsToMany(
            CustomerPhotoUpload::class,
            'customer_photo_marketing_types',
            'marketing_type_id',
            'customer_photo_id'
        );
    }
}
