<?php

namespace App\Modules\Shop\Services;


use App\Modules\Shop\Models\License;
use App\Modules\Shop\Models\Product;

class LicenseService
{
    public static function createLicenseFromProduct(Product $product, $duration)
    {
        $license = new License;
        $license->product_id = $product->id;
        $license->deliverable_type = $product->type;
        $license->status = 'new';
        $license->duration = $duration;
        $license->save();

        return $license;
    }
}

