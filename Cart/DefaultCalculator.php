<?php

namespace App\Modules\Shop\Cart;

use App\Modules\Shop\Cart\Contracts\Calculator;

class DefaultCalculator implements Calculator
{
    public static function getAttribute(string $attribute, CartItem $cartItem)
    {
        $decimals = config('shop.format.decimals', 2);

        switch ($attribute) {
            case 'discount':
                return $cartItem->price * ($cartItem->getDiscountRate() / 100);
            case 'tax':
                return round($cartItem->priceTarget * ($cartItem->taxRate / 100), $decimals);
            case 'priceTax':
                return round($cartItem->priceTarget + $cartItem->tax, $decimals);
            case 'shippingTotal':
                return round($cartItem->shipping * $cartItem->qty, $decimals);
            case 'discountTotal':
                return round($cartItem->discount * $cartItem->qty, $decimals);
            case 'priceTotalHw':
                return round($cartItem->price * $cartItem->qty, $decimals);
            case 'priceTotalActivation':
                return round($cartItem->priceActivation * $cartItem->qty, $decimals);
            case 'priceTotal':
                return round(($cartItem->price + $cartItem->priceActivation) * $cartItem->qty, $decimals);
            case 'subtotalHw':
                return max(round($cartItem->priceTotalHw - $cartItem->discountTotal , $decimals), 0);
            case 'subtotalActivation':
                return max(round($cartItem->priceTotalActivation - $cartItem->discountTotal , $decimals), 0);
            case 'subtotal':
                return max(round($cartItem->priceTotal - $cartItem->discountTotal , $decimals), 0);
            case 'priceTarget':
                return round(($cartItem->priceTotal + $cartItem->shippingTotal - $cartItem->discountTotal) / $cartItem->qty, $decimals);
            case 'taxTotal':
                return round(($cartItem->subtotal + $cartItem->shippingTotal) * ($cartItem->taxRate / 100), $decimals);
            case 'total':
                return round($cartItem->subtotal + $cartItem->shippingTotal + $cartItem->taxTotal, $decimals);
            default:
                return;
        }
    }
}
