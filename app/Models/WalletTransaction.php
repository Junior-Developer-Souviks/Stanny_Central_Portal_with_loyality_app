<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = "wallet_transactions";
    protected $fillable = [
            'user_id', 'type', 'points','lounge_visits','lounge_before','lounge_after','lounge_used','balance_before','balance_after', 'source', 'channel', 'expiry_date'
        ];
}
