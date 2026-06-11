<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaCustomer extends Model
{
    protected $table = 'proforma_customers';
    protected $fillable = [
        'name', 
        'address', 
        'email', 
        'country_code', 
        'mobile',
        
    ];
}
