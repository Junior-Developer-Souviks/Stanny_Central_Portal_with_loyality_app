<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualInvoiceItem extends Model
{
    protected $table = 'manual_invoice_items';
    protected $fillable = [
        'manual_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function manualInvoice()
    {
        return $this->belongsTo(ManualInvoice::class);
    }
}
