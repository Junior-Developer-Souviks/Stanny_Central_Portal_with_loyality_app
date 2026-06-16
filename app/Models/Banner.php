<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = "banners";
    protected $fillable = [
            'title', 'image', 'display_order', 'status', 'created_by'
        ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
