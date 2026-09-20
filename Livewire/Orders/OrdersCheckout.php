<?php

namespace App\Modules\Shop\Livewire\Orders;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use Livewire\Component;

class OrdersCheckout extends Component
{
    use Authorize;

    public Order $order;

    public function booted(): void
    {
        $this->authorize('admin|pay own orders|view own orders');

        if ($this->order->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }

    public function mount(Order $order): void
    {
        $this->order = $order;

        if ($order->status !== 'pending_payment') {
            $this->redirect(route_lang('orders.view', $order));
        }
    }

    public function render()
    {
        return view('shop::orders.orders_checkout', [
            'order' => $this->order,
        ])->layout('shop::frontend');
    }
}
