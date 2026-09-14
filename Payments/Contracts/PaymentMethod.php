<?php

namespace App\Modules\Shop\Payments\Contracts;

use App\Modules\Shop\Payments\PaymentStart;

/**
 * A way to pay a Payable (an order, a subscription period). The shop ships
 * ManualPayment (the operator collects offline); zofe/payments-module registers
 * its gateways; your own: implement this and list the class in
 * config('shop.payment_methods').
 */
interface PaymentMethod
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /** A Font Awesome class, e.g. "fa-credit-card". */
    public function icon(): string;

    /** Offer this method for the payable? (physical goods, recurring, currency, credentials…) */
    public function available(Payable $payable): bool;

    /** The customer chose it: redirect to the gateway, or a message for an offline payment. $payment: the pending local record, if any. */
    public function start(Payable $payable, ?object $payment = null): PaymentStart;
}
