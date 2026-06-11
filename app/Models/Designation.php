<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;
    protected $table = "designation";
    protected $fillable = [
        'name'
    ];
    
    public function users()
    {
        return $this->hasMany(User::class, 'designation', 'id');
    }

      // Relationship with Role model via User_Role
      public function roles()
      {
          return $this->belongsToMany(Role::class, 'user_roles', 'designation_id', 'role_id');
      }
      public function permissions()
      {
          return $this->belongsToMany(Permission::class, 'designation_permissions');
      }
     

}
