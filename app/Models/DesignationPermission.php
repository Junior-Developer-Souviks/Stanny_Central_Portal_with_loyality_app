<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DesignationPermission extends Model
{
    use HasFactory;
    protected $fillable = [
        'designation_id','permission_id'
    ];
    public function designations()
    {
        return $this->belongsToMany(Designation::class);
    }
    public function permissions()
    {
        return $this->belongsToMany(Permissions::class);
    }
}
