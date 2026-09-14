<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Payments\PaymentMethods;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/** The customer's checkout: the order and the payment methods offered for it. */
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

    public function pay(string $key): void
    {
        $method = app(PaymentMethods::class)->find($key);
        if (! $method || $this->order->status !== 'pending_payment' || ! $method->available($this->order)) {
            return;
        }

        try {
            $pending = app(\App\Modules\Shop\Payments\Contracts\PaymentRecorder::class)->findPending($this->order);
            $start = $method->start($this->order, $pending);
        } catch (\RuntimeException $e) {
            session()->flash('checkout_message', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('checkout: ' . $e->getMessage(), ['method' => $key, 'order_id' => $this->order->id, 'exception' => $e]);
            session()->flash('checkout_message', 'Payment could not be started. Please try again.');
            return;
        }

        if ($start->url) {
            $this->redirect($start->url);
            return;
        }

        $this->order->refresh();
        session()->flash('checkout_message', $start->message);
    }

    public function render()
    {
        return view('shop::orders.orders_checkout_embed', [
            'order'   => $this->order,
            'methods' => app(PaymentMethods::class)->for($this->order),
        ]);
    }
}
