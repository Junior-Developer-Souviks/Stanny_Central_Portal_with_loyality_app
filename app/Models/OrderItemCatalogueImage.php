<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemCatalogueImage extends Model
{
    protected $table = "order_item_catalogue_images";
    protected $fillable = [
        'order_item_id',
        'image_path'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_item_id','id');
    }

}
