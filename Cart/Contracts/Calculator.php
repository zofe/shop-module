<?php

namespace App\Modules\Shop\Cart\Contracts;

use Illuminate\Support\Collection;

interface Calculator
{
    public static function getAttribute(string $attribute, BuyableItem $cartItem);

    public function calculateDiscount(Collection $items): float;
    public function calculateSubtotal(Collection $items): float;

    public function calculateTax(Collection $items): float;

    public function calculateShipping(Collection $items): float;
    public function calculateTotal(Collection $items): float;
}
