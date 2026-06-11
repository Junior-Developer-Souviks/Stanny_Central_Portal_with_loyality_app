<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ChangeTracker;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'catalogue_id', 'cat_page_number', 'cat_page_item', 'order_id', 'product_id', 'collection', 'fabrics', 'fittings', 'priority_level', 'expected_delivery_date', 'category', 'sub_category', 'quantity', 'piece_price', 'product_name', 'total_price', 'vents', 'vents_required', 'vents_count', 'fold_cuff_required', 'fold_cuff_size', 'pleats_required', 'pleats_count', 'back_pocket_required', 'back_pocket_count', 'adjustable_belt', 'suspender_button', 'trouser_position', 'sleeves', 'collar', 'collar_style', 'pocket', 'cuffs', 'cuff_style', 'remarks', 'status', 'tl_status', 'admin_status', 'assigned_team','client_name_required','client_name_place','client_name_options','shoulder_type','mens_hand_stitching','ladies_hand_stitching','received_at'
    ];

    public function catalogue()
    {
        return $this->belongsTo(Catalogue::class, 'catalogue_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection', 'id');
    }

    public function collectionType()
    {
        return $this->belongsTo(Collection::class, 'collection', 'id');
    }

    public function measurements()
    {
        return $this->hasMany(OrderMeasurement::class);
    }



    //     public function collection()
    // {
    //     return $this->belongsTo(Collection::class, 'collection','id');
    // }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category','id');
    }
    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabrics','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }

    public function images()
    {
        return $this->hasMany(OrderItemImage::class,'order_item_id');
    }

     public function deliveries(){
        return $this->hasMany(Delivery::class);
    }
    public function getDeliveredQtyAttribute()
    {
        return $this->deliveries_sum_delivered_quantity
            ?? $this->deliveries()->sum('delivered_quantity');
    }
    public function voice_remark()
    {
        return $this->hasMany(OrderItemVoiceMessage::class, 'order_item_id', 'id');
    }
    
    public function catlogue_image()
    {
        return $this->hasMany(OrderItemCatalogueImage::class, 'order_item_id', 'id');
    }
    protected static function booted(): void
    {
        static::updating(function ($item) {
            $original = $item->getOriginal();
            $dirty = $item->getDirty();

            $normalize = function ($value) {
                if (is_null($value)) return 0;
                if (is_numeric($value)) return (float)$value;
                try {
                    return (new \DateTime($value))->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return $value;
                }
            };

            $before = [];
            $after = [];

            foreach ($dirty as $key => $value) {
                $normOld = $normalize($original[$key] ?? null);
                $normNew = $normalize($value);
                if ($normOld !== $normNew) {
                    $before[$key] = $normOld;
                    $after[$key] = $normNew;
                }
            }

            if (!empty($before)) {
                $orderId = ChangeTracker::getOrderId();

                if ($orderId) {

                    ChangeTracker::add("items", [
                        'order_id' => $orderId,
                        'id'       => $item->id,
                        'before'   => $before,
                        'after'    => $after,
                    ]);

                }
            }
        });
    }


}
