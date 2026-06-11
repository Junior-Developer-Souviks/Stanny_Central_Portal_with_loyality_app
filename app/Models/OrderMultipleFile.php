<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMultipleFile extends Model
{
    protected $table = "order_multiple_files";
    protected $fillable = [
        'order_id',
        'file_type',
        'file_path'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id','id');
    }

}
