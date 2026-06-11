<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRevoke extends Model
{
    protected $table = "payment_revokes";
    protected $fillable =[
        'customer_id', 'done_by', 'voucher_no', 'collection_amount', 'paymentcollection_data_json'
    ];
}
