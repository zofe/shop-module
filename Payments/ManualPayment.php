<?php

namespace App\Modules\Shop\Payments;

use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Payments\Contracts\PaymentMethod;

/**
 * The payment happens outside the shop: bank transfer, a PayPal link sent by
 * hand, cash on delivery… The order moves to "payment verification" and the
 * customer reads the instructions of config('shop.manual_payment'); an operator
 * confirms with the "payment done" transition on the order page.
 */
class ManualPayment implements PaymentMethod
{
    public function key(): string
    {
        return 'manual';
    }

    public function label(): string
    {
        return config('shop.manual_payment.label', 'Bank transfer');
    }

    public function description(): string
    {
        return config('shop.manual_payment.description', 'We will send you the payment details');
    }

    public function icon(): string
    {
        return config('shop.manual_payment.icon', 'fa-university');
    }

    public function available(Order $order): bool
    {
        return (bool) config('shop.manual_payment.enabled', true);
    }

    public function start(Order $order): PaymentStart
    {
        $workflow = \Workflow::get($order, 'order');
        if ($workflow->can($order, 'check_payment')) {
            $workflow->apply($order, 'check_payment');
            $order->save();
        }

        return PaymentStart::message($this->instructions($order));
    }

    /** The instructions with the order's data filled in. */
    public function instructions(Order $order): string
    {
        $text = config('shop.manual_payment.instructions', 'Thank you. Your order {order} of {total} is registered: we will contact you at {email} with the payment details.');

        return strtr($text, [
            '{order}' => $order->shortId ?? $order->id,
            '{total}' => number_format((float) $order->total, 2) . ' ' . Cart::currency(),
            '{email}' => $order->user?->email ?? '',
        ]);
    }
}
