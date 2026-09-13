<?php

namespace App\Modules\Shop\Tax\Contracts;

use App\Modules\Shop\Tax\TaxContext;
use App\Modules\Shop\Tax\TaxResult;

/**
 * Estimates the tax of a sale from the customer's billing data. The estimate is
 * what the cart and the order show; a payment gateway able to compute taxes
 * (Stripe Tax, Paddle) may replace it with a final result.
 */
interface TaxResolver
{
    public function resolve(TaxContext $context): TaxResult;
}
