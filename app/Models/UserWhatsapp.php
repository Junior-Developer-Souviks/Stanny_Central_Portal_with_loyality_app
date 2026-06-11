<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserWhatsapp extends Model
{
    use HasFactory;
    protected $table = 'user_whatsapps';

    protected $fillable = [
        'supplier_id','user_id', 'country_code', 'whatsapp_number'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
