<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'price_list_item_id', 'name', 'qty', 'price', 'subtotal', 'discountRate', 'taxRate', 'shipping',
        'bundle_code', 'prd_code',
    ];
}
