<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Gallery extends Model
{
    protected $table = "galleries";
    protected $fillable = [
        'product_id',
        'image'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
