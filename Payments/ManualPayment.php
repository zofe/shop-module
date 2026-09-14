<?php

namespace App\Modules\Shop\Payments;

use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Payments\Contracts\Payable;
use App\Modules\Shop\Payments\Contracts\PaymentMethod;

/**
 * The payment happens outside the shop: bank transfer, a PayPal link sent by
 * hand, cash on delivery… The local payment record stays pending until an
 * operator confirms it (order page: "payment done"; subscription page: "mark paid").
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

    public function available(Payable $payable): bool
    {
        return (bool) config('shop.manual_payment.enabled', true);
    }

    public function start(Payable $payable, ?object $payment = null): PaymentStart
    {
        if ($payable instanceof Order) {
            $workflow = \Workflow::get($payable, 'order');
            if ($workflow->can($payable, 'check_payment')) {
                $workflow->apply($payable, 'check_payment');
                $payable->save();
            }
        }
        if ($payment && method_exists($payment, 'forceFill') && ! $payment->gateway) {
            $payment->forceFill(['gateway' => 'manual'])->save();
        }

        return PaymentStart::message($this->instructions($payable));
    }

    /** The instructions with the payable's data filled in. */
    public function instructions(Payable $payable): string
    {
        $text = config('shop.manual_payment.instructions', 'Thank you. {description} ({total}) is registered: we will contact you at {email} with the payment details.');
        $amounts = $payable->payableAmounts();

        return strtr($text, [
            '{order}'       => $payable->payableId(),
            '{description}' => $payable->payableDescription(),
            '{total}'       => number_format((float) ($amounts['total'] ?? 0), 2) . ' ' . Cart::currency(),
            '{email}'       => $payable->payableCustomerEmail() ?? '',
        ]);
    }
}
