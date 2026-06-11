<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaInvoice extends Model
{
    protected $table = 'proforma_invoices';
    protected $fillable = [
        'customer_id', 
        'proforma_number', 
        'date', 
        'subtotal', 
        'total_amount', 
        'conditions'
    ];

    public function customer()
    {
        return $this->belongsTo(ProformaCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(ProformaInvoiceItem::class, 'proforma_id');
    }
}
