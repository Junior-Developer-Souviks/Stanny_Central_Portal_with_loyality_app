<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table = "purchase_orders";

    protected $fillable = [
        'supplier_id', 
        'unique_id', 
        'product_ids', 
        'fabric_ids', 
        'address', 
        'city', 
        'pin', 
        'state', 
        'country', 
        'landmark', 
        'total_price', 
        'is_good_in', 
        'goods_in_type', 
        'status',
        'is_approved'
    ];

     // Relationship with Supplier
     public function supplier()
     {
         return $this->belongsTo(Supplier::class, 'supplier_id');
     }
 
     public function products()
     {
         return $this->belongsToMany(Product::class, 'purchase_order_products', 'purchase_order_id', 'product_id');
     }
 
    //  // Relationship with Fabric (assuming fabric_ids are stored as JSON)
    //  public function fabrics()
    //  {
    //      return $this->belongsToMany(Fabric::class, 'purchase_order_fabrics', 'purchase_order_id', 'fabric_id');
    //  }
    public function orderproducts()
    {
        return $this->hasMany(PurchaseOrderProduct::class, 'purchase_order_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by','id');
    }

}
