<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaInvoiceItem extends Model
{
    protected $table = 'proforma_invoice_items';
    protected $fillable = [
        'proforma_id', 
        'product_id', 
        'quantity', 
        'unit_price', 
        'total_price'
    ];

    public function invoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }   
}
