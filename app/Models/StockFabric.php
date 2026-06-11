<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockFabric extends Model
{
    protected $table = 'stock_fabrics';
    protected $fillable = [
        'stock_id',
        'fabric_id',
        'qty_in_meter',
        'qty_while_grn',
        'piece_price',
        'total_price',
    ];

    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }
}
