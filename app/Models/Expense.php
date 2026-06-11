<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = "expences";
    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'description',
        'for_debit',
        'for_credit',
        'for_staff',
        'for_customer',
        'for_supplier',
    ];
}
