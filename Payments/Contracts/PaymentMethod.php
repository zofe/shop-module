<?php

namespace App\Modules\Shop\Payments\Contracts;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Payments\PaymentStart;

/**
 * A way to pay an order, offered on the checkout page. The shop ships
 * ManualPayment (the operator collects the payment offline); zofe/payments-module
 * registers its gateways; your own: implement this and list the class in
 * config('shop.payment_methods').
 */
interface PaymentMethod
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /** A Font Awesome class, e.g. "fa-credit-card". */
    public function icon(): string;

    /** Offer this method for the order? (currency, amount, configured credentials…) */
    public function available(Order $order): bool;

    /** The customer chose it: redirect to the gateway, or a message for an offline payment. */
    public function start(Order $order): PaymentStart;
}
