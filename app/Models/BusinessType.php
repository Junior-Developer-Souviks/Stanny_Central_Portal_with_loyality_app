<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ChangeTracker;

class BusinessType extends Model
{
    protected $table = "business_types";
    protected $fillable = ['title', 'image'];
    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'product_fabrics', 'fabric_id', 'product_id');
    // }

    public function users()
    {
        return $this->hasMany(User::class, 'business_type_id');
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

                    ChangeTracker::add("businessType", [
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
