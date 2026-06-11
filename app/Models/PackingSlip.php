<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackingSlip extends Model
{
  use HasFactory;

  protected $table = 'packingslips';
    protected $fillable = [
        'order_id', 'customer_id', 'slipno', 'is_disbursed', 'created_by', 'created_at', 'updated_by', 'updated_at', 'disbursed_by', 'disbursed_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}
