<?php

namespace App\Modules\Shop\Services;


use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ServiceItem;

class ServicesService
{
    public static function createServiceItemFromProduct(Product $product)
    {
        $item = new ServiceItem;
        $item->product_id = $product->id;
        $item->status = 'new';
        $item->save();

        return $item;
    }
}

