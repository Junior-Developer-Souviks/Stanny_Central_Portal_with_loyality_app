<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualInvoice extends Model
{
    protected $table = 'manual_invoices';
    protected $fillable = [
        'invoice_no', 
        'customer_name',
         'invoice_date', 
         'due_date', 
         'source', 
         'reference', 
         'total_amount', 
         'ht_amount', 
         'tva_amount', 
         'ca_amount', 
         'paid_amount',
         'due_amount',
    ];

    public function items(){
        return $this->hasMany(ManualInvoiceItem::class,'manual_invoice_id');
    }




    

   
}
