<?php

namespace App\Modules\Shop\Payments;

use App\Modules\Shop\Models\Order;
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

    /** The module is installed and the driver has its credentials. */
    public function available(Order $order): bool
    {
        if (! class_exists(\App\Modules\Payments\PaymentsManager::class)) {
            return false;
        }
        $config = config("payments.{$this->driver}", []);
        if (isset($config['enabled']) && ! $config['enabled']) {
            return false;
        }
        $credential = $config['secret'] ?? $config['token'] ?? $config['vendor_auth_code'] ?? null;

        return ! empty($credential);
    }

    public function start(Order $order): PaymentStart
    {
        $taxRate = (float) ($order->tax_rate ?? config('shop.tax', 22));
        $items = $order->items->map(fn ($item) => [
            'name'          => $item->name,
            'prd_code'      => $item->prd_code ?? null,
            'order_item_id' => $item->id,
            'qty'           => (float) $item->qty,
            'price'         => (float) $item->price,
            'subtotal'      => (float) $item->subtotal,
            'shipping'      => (float) ($item->shipping ?? 0),
            'discountRate'  => (float) ($item->discountRate ?? 0),
            'discount'      => (float) ($item->discount ?? 0),
            'taxRate'       => $taxRate,
            'tax'           => round((float) $item->subtotal * $taxRate / 100, 2),
            'total'         => round((float) $item->subtotal * (1 + $taxRate / 100), 2),
        ])->all();

        $data = new \App\Modules\Payments\Dto\CheckoutData(
            orderId:       $order->id,
            total:         (float) $order->total,
            subtotal:      (float) $order->subtotal,
            tax:           (float) $order->tax,
            shipping:      (float) ($order->shipping ?? 0),
            description:   'Order ' . $order->shortId,
            currency:      config('payments.currency', 'eur'),
            customerEmail: $order->user?->email,
            metadata:      ['order_id' => $order->id],
            items:         $items,
        );

        return PaymentStart::redirect(app(\App\Modules\Payments\PaymentsManager::class)->driver($this->driver)->initiateCheckout($data));
    }
}
