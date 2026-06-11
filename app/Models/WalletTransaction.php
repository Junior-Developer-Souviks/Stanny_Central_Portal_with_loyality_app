<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = "wallet_transactions";
    protected $fillable = [
            'user_id', 'type', 'points','lounge_visits', 'source', 'channel', 'reference_id', 'expiry_date'
        ];
}
