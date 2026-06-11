<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = "branches";
    protected $fillable = [
        'country_id',
        'name',
        'address',
        'email',
        'mobile',
        'whatsapp',
        'city'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class,'country_id','id');
    }

}
