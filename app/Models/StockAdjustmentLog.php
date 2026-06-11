<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentLog extends Model
{
    protected $table = 'stock_adjustment_logs';
    protected $fillable = [
        'fabric_id', 'product_id', 'adjustment', 'old_qty', 'new_qty', 'remarks'
    ];

    public function fabric(){
        return $this->belongsTo(Fabric::class,'fabric_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
