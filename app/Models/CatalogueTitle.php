<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueTitle extends Model
{
    protected $table = 'catalogue_titles';
    protected $fillable = ['title'];

    public function catalogues()
    {
        return $this->hasMany(Catalogue::class, 'catalogue_title_id', 'id');
    }
    
}
