<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ChangeTracker;

class OrderItemVoiceMessage extends Model
{
    protected $table = 'order_item_voice_messages';

    protected $fillable = [
        'order_item_id',
        'voices_path',
        'created_at',
        'updated_at',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_item_id');
    }
    protected static function boot(): void
    {
        parent::boot(); // ✅ MUST be first

        // static::created(function ($message) {
        //    ChangeTracker::add('voice_messages', [
        //         'order_id'      =>ChangeTracker::getOrderId(),
        //         'order_item_id' => $message->order_item_id,
        //         'id'            => $message->id,
        //         'action'        => 'created',
        //         'data'          => [
        //             'filename' => $message->voices_path,
        //             'action'   => 'created',
        //         ],
        //     ]);
        // });

        // static::deleted(function ($message) {
        //     ChangeTracker::add('voice_messages', [
        //         'order_id'      =>ChangeTracker::getOrderId(),
        //         'order_item_id' => $message->order_item_id,
        //         'id'            => $message->id,
        //         'action'        => 'deleted',
        //         'data'          => [
        //             'filename' => $message->voices_path,
        //             'action'   => 'deleted',
        //         ],
        //     ]);
        // });
    }


}
