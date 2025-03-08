<?php

namespace App\Modules\Shop\Services;

use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\Order;

class OrderService
{
    public static function createOrderFromCart($note = null, $identifier = null, $company_id = null, $expected_dealer_id = null)
    {
        $company = $company_id ? Company::find($company_id) : optional(auth()->user())->company;
        if ($company_id) {
            $company = Company::find($company_id);
            $user = optional($company)->owner;
        } elseif (auth()->user() && auth()->user()->company) {
            $company = auth()->user()->company;
            $user = auth()->user();
        } else {
            throw new \InvalidArgumentException('createOrderFromCart: Please supply a valid company id');
        }

        $order = new Order();
        $order->id = (string) Cart::uuid();
        $order->user_id = $user->id;
        $order->company_id = $company->id;
        $order->expected_dealer_id = $expected_dealer_id;
        $order->price_list_id = $company->pricelist_id ?? 1;

        $order->discount = Cart::discountFloat();
        $order->subtotal = Cart::subtotalFloat();

        $taxRate = ($company) ? (int) $company->tax_perc : config('shop.tax');
        $tax = (Cart::subtotalFloat() + Cart::shippingFloat()) * $taxRate / 100;
        $total = round(Cart::subtotalFloat() + Cart::shippingFloat() + $tax, 2);

        $order->tax = round($tax, 2); //Cart::taxFloat();
        $order->shipping = Cart::shippingFloat();
        $order->total = $total;
        $order->note = $note;

        $order->save();

        if (! $order) {
            return false;
        }

        activity('create_order')
            ->performedOn($order)
            ->causedBy($user)
            ->by($user)
            ->withProperties($order->toArray())
            ->log('l\'utente '.$user->fullName.' ha creato l\'ordine '.$order->shortId);

        self::addItemsFromCart('default', $order);
        self::assignBundledLicenses($order);
        self::assignBundledServices($order);

        return $order;
    }
}
