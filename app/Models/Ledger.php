<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ledger extends Model
{
    use HasFactory;

    protected $table = 'ledgers';

    protected $fillable = [
        'user_type', 'staff_id', 'customer_id', 'supplier_id', 'admin_id', 
        'payment_id', 'staff_commision_id', 'collection_staff_commission_id', 
        'store_bad_debt_id', 'transaction_id', 'transaction_amount', 
        'is_credit', 'is_debit', 'bank_cash', 'entry_date', 'purpose', 
        'purpose_description', 'start_date', 'whatsapp_status', 
        'last_whatsapp', 'created_at', 'updated_at'
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}

