<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPhotoMarketingType extends Model
{
    protected $table = "customer_photo_marketing_types";
    protected $fillable = [
           'customer_photo_id','marketing_type_id'
        ];
}
