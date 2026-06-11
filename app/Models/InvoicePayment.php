<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoicePayment extends Model
{
  use HasFactory;
  protected $table = 'invoice_payments';
  protected $fillable = [
     'invoice_id', 'payment_collection_id', 'invoice_amount', 'vouchar_amount', 'paid_amount', 'rest_amount', 'is_commisionable', 'invoice_no', 'voucher_no', 'created_at', 'updated_at'
  ];

    public function collection(){
        return $this->belongsTo(PaymentCollection::class, 'payment_collection_id', 'id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
    
}
