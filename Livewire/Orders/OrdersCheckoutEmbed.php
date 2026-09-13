<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Payments\Dto\CheckoutData;
use App\Modules\Payments\PaymentsManager;
use App\Modules\Shop\Models\Order;
use Illuminate\Support\Facades\Log;
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

        if (! class_exists(PaymentsManager::class)) {
            session()->flash('checkout_message', 'Online payments are not available: the payments module is not installed.');
            return;
        }

        try {
            $taxRate = $this->order->tax_rate ?? config('shop.tax', 22);
            $items = $this->order->items->map(fn ($item) => [
                'name'         => $item->name,
                'prd_code'     => $item->prd_code ?? null,
                'order_item_id' => $item->id,
                'qty'          => (float) $item->qty,
                'price'        => (float) $item->price,
                'subtotal'     => (float) $item->subtotal,
                'shipping'     => (float) ($item->shipping ?? 0),
                'discountRate' => (float) ($item->discountRate ?? 0),
                'discount'     => (float) ($item->discount ?? 0),
                'taxRate'      => (float) $taxRate,
                'tax'          => round((float) $item->subtotal * $taxRate / 100, 2),
                'total'        => round((float) $item->subtotal * (1 + $taxRate / 100), 2),
            ])->all();

            $data = new CheckoutData(
                orderId:       $this->order->id,
                total:         (float) $this->order->total,
                subtotal:      (float) $this->order->subtotal,
                tax:           (float) $this->order->tax,
                shipping:      (float) ($this->order->shipping ?? 0),
                description:   'Order ' . $this->order->shortId,
                currency:      config('payments.currency', 'eur'),
                customerEmail: auth()->user()->email,
                metadata:      ['order_id' => $this->order->id],
                items:         $items,
            );

            $url = app(PaymentsManager::class)->driver($gateway)->initiateCheckout($data);

            $this->redirect($url);
        } catch (\RuntimeException $e) {
            session()->flash('checkout_message', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('initiatePayment failed', [
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
