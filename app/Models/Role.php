<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = "roles";

    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'user_roles', 'role_id', 'designation_id');
    }
}
