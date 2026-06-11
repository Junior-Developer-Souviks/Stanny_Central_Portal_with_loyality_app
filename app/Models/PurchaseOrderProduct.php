<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProduct extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_products';

    protected $fillable = [
        'purchase_order_id', 
        'collection_id', 
        'stock_type', 
        'piece_price', 
        'total_price', 
        'fabric_id', 
        'fabric_name', 
        'qty_in_meter', 
        'qty_while_grn_fabric',
        'product_id', 
        'product_name', 
        'qty_in_pieces',
        'qty_while_grn_product'
    ];

    // Relationship with PurchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relationship with Fabric
    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }

    // Relationship with Collection
    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }
}
