<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $table = "invoice_products";
    protected $fillable =[
         'invoice_id', 'product_id','order_item_id', 'product_name', 'quantity', 'single_product_price', 'total_price', 'is_store_address_outstation'
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
