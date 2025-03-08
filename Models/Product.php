<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';


    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }


    public function getFullPathAttribute()
    {
        return $this->category->full_path . '/' . $this->slug;
    }
}
