<?php

namespace App\Modules\Shop\Cart\Contracts;

use App\Modules\Shop\Cart\CartItem;

interface Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem);
}
