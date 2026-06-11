<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    protected $fillable = [
        'order_id', 'customer_id', 'user_id', 'packingslip_id', 'invoice_no', 'net_price', 'required_payment_amount', 'payment_status', 'is_paid', 'created_by', 'updated_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
   
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function packing()
    {
        return $this->belongsTo(PackingSlip::class, 'packingslip_id', 'id');
    }
    
    public function billingAddressLatest()
    {
        return $this->hasOneThrough(UserAddress::class, User::class, 'id', 'user_id', 'customer_id', 'id')
                    ->where('address_type', 1)
                    ->latestOfMany();
    }


}
