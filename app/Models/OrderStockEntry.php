<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStockEntry extends Model
{
    protected $table = 'order_stock_entries';
    protected $fillable = [
        'order_id', 
        'order_item_id', 
        'product_id', 
        'fabric_id', 
        'quantity', 
        'extra_meter',
        'unit', 
        'previous_quantity',
        'created_by'        
    ];
}
