<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends Model
{
    use HasFactory;
    protected $table = 'journals';

    protected $fillable = [
         'payment_id', 'transaction_amount', 'is_credit', 'is_debit', 'bank_cash', 'purpose', 'purpose_description', 'purpose_id', 'entry_date', 'is_gst', 'created_at', 'updated_at'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }
}
