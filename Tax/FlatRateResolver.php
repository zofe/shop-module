<?php

namespace App\Modules\Shop\Tax;

use App\Modules\Shop\Tax\Contracts\TaxResolver;

/** One rate for everybody: config('shop.tax'). The behaviour of the shop before 2.x. */
class FlatRateResolver implements TaxResolver
{
    public function resolve(TaxContext $context): TaxResult
    {
        return new TaxResult((float) config('shop.tax', 22), 'flat', 'flat');
    }
}
