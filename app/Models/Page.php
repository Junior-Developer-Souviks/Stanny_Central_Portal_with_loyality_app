<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = "pages";
    protected $fillable = ['catalogue_id', 'page_number'];

    public function catalogue(){
        return $this->belongsTo(Catalogue::class,'catalogue_id');
    }

    public function cataloguePageItems(){
        return $this->hasMany(CataloguePageItem::class,'page_id');
    }
}
