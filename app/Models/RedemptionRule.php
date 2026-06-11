<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedemptionRule extends Model
{
    protected $table = "redemption_rules";
    protected $fillable = [
        'channel',
        'ratio',
        'status'
    ];
}
