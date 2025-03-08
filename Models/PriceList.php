<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{

    protected $table = 'price_lists';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(PriceListItem::class)->orderBy('product_id');
    }
}
