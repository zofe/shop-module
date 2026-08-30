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

        if (! class_exists(\App\Modules\Payments\PaymentsManager::class)) {
            session()->flash('checkout_message', 'Payments module not installed.');
            return;
        }

        try {
            $manager = app(\App\Modules\Payments\PaymentsManager::class);
            $driver  = $manager->driver($gateway);

            if ($gateway === 'stripe') {
                $payment = \App\Modules\Payments\Models\Payment::create([
                    'description'  => 'Order ' . $this->order->shortId,
                    'gateway'      => 'stripe',
                    'status'       => 'pending',
                    'payment_type' => 'order',
                    'order_id'     => $this->order->id,
                    'subtotal'     => $this->order->subtotal,
                    'shipping'     => $this->order->shipping,
                    'tax'          => $this->order->tax,
                    'total'        => $this->order->total,
                ]);

                $session = $driver->startCheckoutSession(
                    amount:     (float) $this->order->total,
                    description: 'Order ' . $this->order->shortId,
                    successUrl: route('payments.stripe.success', $payment),
                    cancelUrl:  route('payments.stripe.cancel',  $payment),
                    options: [
                        'currency'       => strtolower(config('payments.currency', 'eur')),
                        'customer_email' => auth()->user()->email,
                        'metadata'       => ['order_id' => $this->order->id, 'payment_id' => $payment->id],
                    ]
                );

                $payment->update(['gateway_id' => $session['session_id']]);

                $this->dispatch('payment-initiated', orderId: $this->order->id, paymentId: $payment->id, gateway: $gateway);

                $this->redirect($session['url']);
                return;
            }

            if ($gateway === 'gocardless') {
                // GoCardless requires an active mandate before charging.
                // The mandate flow (redirect to GoCardless authorization page) is
                // handled by the MandateFlow component — not yet implemented.
                session()->flash('checkout_message', 'GoCardless: mandate flow not yet available.');
                return;
            }

            if ($gateway === 'paddle') {
                // Paddle uses a client-side JS overlay (Paddle.js).
                // Not yet implemented — requires laravel/cashier-paddle and frontend integration.
                session()->flash('checkout_message', 'Paddle checkout coming soon.');
                return;
            }

        } catch (\RuntimeException $e) {
            // Gateway package not installed
            session()->flash('checkout_message', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('initiatePayment failed', [
                'gateway'  => $gateway,
                'order_id' => $this->order->id,
                'error'    => $e->getMessage(),
            ]);
            session()->flash('checkout_message', 'Payment could not be initiated. Please try again.');
        }
    }

    public function render()
    {
        return view('shop::orders.orders_checkout_embed', [
            'order'    => $this->order,
            'gateways' => $this->availableGateways(),
        ]);
    }
}
