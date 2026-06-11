<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FabricCategory extends Model
{
    protected $table = "fabric_categories";
    protected $fillable = [
        'title'
    ];
}
