<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = ['parent_name','name','route'];
 
    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'designation_permissions');
    }
}
