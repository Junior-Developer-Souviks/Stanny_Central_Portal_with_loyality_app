<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    protected $table = "fabrics";
    protected $fillable = ['collection_id','fabric_category_id','title','pseudo_name', 'image','threshold_price' ,'status'];

    public function products()  
    {
        return $this->belongsToMany(Product::class, 'product_fabrics', 'fabric_id', 'product_id');
    }
    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id', 'id');
    }

    public function fabric_category(){
        return $this->belongsTo(FabricCategory::class, 'fabric_category_id','id');
    }
}
