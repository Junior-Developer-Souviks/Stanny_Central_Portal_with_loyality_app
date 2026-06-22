<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDraft extends Model
{
    use HasFactory;
    
    protected $fillable = ['admin_id','order_number','draft_data', 'expires_at'];

    protected $casts = [
        'draft_data' => 'array',
        'expires_at' => 'datetime',
    ];
}
