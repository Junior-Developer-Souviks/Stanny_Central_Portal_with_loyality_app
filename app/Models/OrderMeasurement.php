<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMeasurement extends Model
{
    use HasFactory;

    protected $table = "order_measurements";
    protected $fillable = [
        'order_item_id', 'measurement_name','measurement_title_prefix', 'measurement_value','remarks', 'created_at', 'updated_at'
    ];
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    public function measurement()
{
    return $this->belongsTo(Measurement::class, 'measurement_name', 'title'); // Ensure you use the correct foreign key
}
protected static function booted(): void
{
    static::updating(function ($measurement) {
        $original = $measurement->getOriginal();
        $dirty = $measurement->getDirty();

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
            $orderId = \App\Services\ChangeTracker::getOrderId();
            if ($orderId) {
                \App\Services\ChangeTracker::add("measurements", [
                   'order_id'       => $orderId,
                        'order_item_id'  => $measurement->order_item_id, // for nesting under items
                        'id'             => $measurement->id,             // measurement id
                        'before'         => $before,
                        'after'          => $after,
                ]);
            }
        }
    });
}


}
