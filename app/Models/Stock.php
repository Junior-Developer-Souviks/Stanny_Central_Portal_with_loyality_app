<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{

    protected $table = 'stocks';
    protected $fillable = [
        'grn_no',
        'purchase_order_id',
        'po_unique_id',
        'return_id',
        'return_order_no',
        'goods_in_type',
        'product_ids',
        'fabric_ids',
        'total_price',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
