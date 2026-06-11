<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Role extends Model
{
    use HasFactory;
    protected $table = "user_roles";
    
    protected $fillable = ['designation_id', 'role_id'];

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
}
