<?php

namespace App\Modules\Shop\Payments;

use App\Modules\Shop\Payments\Contracts\Payable;
use App\Modules\Shop\Payments\Contracts\PaymentMethod;

/**
 * A gateway of zofe/payments-module (Stripe, GoCardless, Paddle…) offered at
 * checkout. ShopServiceProvider registers one per entry of
 * config('shop.gateway_methods') when that module is installed.
 */
class GatewayPaymentMethod implements PaymentMethod
{
    public function __construct(
        protected string $driver,
        protected array $meta = [],
    ) {
    }

    public function key(): string
    {
        return $this->driver;
    }

    public function label(): string
    {
        return $this->meta['label'] ?? ucfirst($this->driver);
    }

    public function description(): string
    {
        return $this->meta['description'] ?? '';
    }

    public function icon(): string
    {
        return $this->meta['icon'] ?? 'fa-credit-card';
    }

    /** The module is installed, the driver has its credentials, and it sells what this is (Paddle: digital only). */
    public function available(Payable $payable): bool
    {
        if (! class_exists(\App\Modules\Payments\PaymentsManager::class)) {
            return false;
        }
        $config = config("payments.{$this->driver}", []);
        if (isset($config['enabled']) && ! $config['enabled']) {
            return false;
        }
        if ($this->driver === 'paddle' && collect($payable->payableItems())->contains(fn ($i) => ($i['deliverable_type'] ?? null) === 'inventory_item')) {
            return false;
        }
        $credential = $config['secret'] ?? $config['token'] ?? $config['vendor_auth_code'] ?? null;

        return ! empty($credential);
    }

    public function start(Payable $payable, ?object $payment = null): PaymentStart
    {
        $amounts = $payable->payableAmounts();
        $links = $payable->payableLinks();

        $data = new \App\Modules\Payments\Dto\CheckoutData(
            orderId:       $links['order_id'] ?? $links['subscription_id'] ?? $links['ref_subscription_id'] ?? $payable->payableId(),
            total:         (float) ($amounts['total'] ?? 0),
            subtotal:      (float) ($amounts['subtotal'] ?? 0),
            tax:           (float) ($amounts['tax'] ?? 0),
            shipping:      (float) ($amounts['shipping'] ?? 0),
            description:   $payable->payableDescription(),
            currency:      config('payments.currency', 'eur'),
            customerEmail: $payable->payableCustomerEmail(),
            metadata:      array_merge($links, ['payable' => $payable->payableType()]),
            items:         $payable->payableItems(),
            paymentId:     $payment?->id,
        );

        return PaymentStart::redirect(app(\App\Modules\Payments\PaymentsManager::class)->driver($this->driver)->initiateCheckout($data));
    }
}
