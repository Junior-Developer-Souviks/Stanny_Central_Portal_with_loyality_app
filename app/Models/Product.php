<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = "products";

    protected $fillable = [
        'collection_id',
        'category_id',
        'sub_category_id',
        'name',
        'product_code',
        'short_description',
        'description',
        'gst_details',
        'product_image',
    ];

    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }

    public function sub_category(){
        return $this->belongsTo(SubCategory::class,'sub_category_id');
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class,'collection_id');
    }
    
    public function fabrics()
    {
        return $this->belongsToMany(Fabric::class, 'product_fabrics', 'product_id', 'fabric_id');
    }
    
    
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }
   
    // public function suppliers()
    // {
    //     return $this->belongsToMany(Supplier::class, 'supplier_products', 'product_id', 'supplier_id');
    // }
  
    
}
