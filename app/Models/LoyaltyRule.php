<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRule extends Model
{
    protected $table = "loyalty_rules";
    protected $fillable = [
           'min_amount', 'max_amount', 'reward_type', 'lounge_visits', 'points_type', 'points_value','points_expiry_days','lounge_expiry_days','effective_date', 'status'
        ];
}
