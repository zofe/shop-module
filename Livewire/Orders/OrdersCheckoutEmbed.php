<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Shop\Models\Order;
use Livewire\Component;

class OrdersCheckoutEmbed extends Component
{
    public Order $order;

    public function booted(): void
    {
        if (auth()->guest() || $this->order->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function mount(Order $order): void
    {
        $this->order = $order;
    }

    public function availableGateways(): array
    {
        return config('shop.payment_gateways', []);
    }

    public function initiatePayment(string $gateway): void
    {
        if (! array_key_exists($gateway, $this->availableGateways())) {
            return;
        }

        if ($this->order->status !== 'pending_payment') {
            return;
        }

        // TODO: delegate to PaymentsManager::driver($gateway)->initiateCheckout($order, $successUrl, $cancelUrl)
        // Each driver handles its own flow (Stripe Checkout Session, GCL mandate, Paddle overlay).
        // The embed must not contain gateway-specific logic.
        $this->dispatch('payment-initiated', orderId: $this->order->id, gateway: $gateway);

        session()->flash('checkout_message', "Payment via {$gateway} — coming soon.");
    }

    public function render()
    {
        return view('shop::orders.orders_checkout_embed', [
            'order'    => $this->order,
            'gateways' => $this->availableGateways(),
        ]);
    }
}
