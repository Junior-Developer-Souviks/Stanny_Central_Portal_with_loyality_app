<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class ChangeLog extends Model
{
    protected $table = 'changelog';
    protected $fillable = [
        'done_by',
        'order_id',
         'purpose',
         'data_details'
    ];

public function order()
{
    return $this->belongsTo(Order::class);
}
public function user()
{
    return $this->belongsTo(User::class, 'done_by', 'id');;
}

}
