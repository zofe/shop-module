<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $casts = [
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
