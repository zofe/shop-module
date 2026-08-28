<?php

namespace App\Modules\Shop\Cart;

use App\Modules\Shop\Cart\Contracts\BuyableItem;
use App\Modules\Shop\Cart\Contracts\Calculator;
use Illuminate\Support\Collection;


class DefaultCalculator implements Calculator
{
    public static function getAttribute(string $attribute, BuyableItem $item)
    {
        $decimals = config('cart.format.decimals', 2);

        // Calcola le proprietà base necessarie
        $priceTotal = round(($item->price + ($item->priceActivation ?? 0)) * $item->qty, $decimals);
        $discountTotal = round($item->price * ($item->getDiscountRate() / 100) * $item->qty, $decimals);
        $shippingTotal = round($item->shipping * $item->qty, $decimals);

        switch ($attribute) {
            case 'discount':
                return $item->price * ($item->getDiscountRate() / 100);
            case 'tax':
                $priceTarget = round(($priceTotal + $shippingTotal - $discountTotal) / $item->qty, $decimals);
                return round($priceTarget * ($item->taxRate / 100), $decimals);
            case 'priceTax':
                $priceTarget = round(($priceTotal + $shippingTotal - $discountTotal) / $item->qty, $decimals);
                return round($priceTarget + ($priceTarget * ($item->taxRate / 100)), $decimals);
            case 'shippingTotal':
                return $shippingTotal;
            case 'discountTotal':
                return $discountTotal;
            case 'priceTotalHw':
                return round($item->price * $item->qty, $decimals);
            case 'priceTotalActivation':
                return round(($item->priceActivation ?? 0) * $item->qty, $decimals);
            case 'priceTotal':
                return $priceTotal;
            case 'subtotalHw':
                return max(round(($item->price * $item->qty) - $discountTotal, $decimals), 0);
            case 'subtotalActivation':
                return max(round(($item->priceActivation ?? 0) * $item->qty - $discountTotal, $decimals), 0);
            case 'subtotal':
                return max(round($priceTotal - $discountTotal, $decimals), 0);
            case 'priceTarget':
                return round(($priceTotal + $shippingTotal - $discountTotal) / $item->qty, $decimals);
            case 'taxTotal':
                return round(($priceTotal - $discountTotal + $shippingTotal) * ($item->taxRate / 100), $decimals);
            case 'total':
                $subtotal = max(round($priceTotal - $discountTotal, $decimals), 0);
                return round($subtotal + $shippingTotal + ($subtotal + $shippingTotal) * ($item->taxRate / 100), $decimals);
            default:
                return null;
        }
    }


    public function calculateDiscount(Collection $items): float
    {
        return $this->sumItems($items, 'discountTotal');
    }

    public function calculateSubtotal(Collection $items): float
    {
        return $this->sumItems($items, 'subtotal');
    }

    public function calculateTotal(Collection $items): float
    {
        return $this->sumItems($items, 'total');
    }

    public function calculateShipping(Collection $items): float
    {
        return $this->sumItems($items, 'shippingTotal');
    }

    public function calculateTax(Collection $items): float
    {
        return $this->sumItems($items, 'taxTotal');
    }

    private function sumItems(Collection $items, string $attribute): float
    {
        return $items->reduce(function ($sum, $item) use ($attribute) {
            $value = $item->{$attribute} ?? self::getAttribute($attribute, $item);
            return $sum + (float) $value;
        }, 0);
    }
}
