<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Shop\Models\Order;

class CustomerOrdersView extends OrdersView
{
    public function booted()
    {
        $this->authorize('view own orders|pay own orders');

        // Ensure the customer can only view their own orders
        if ($this->order && $this->order->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function render()
    {
        return view('shop::orders.customer_orders_view', [
            'order' => $this->order,
        ])->layout('shop::frontend');
    }
}
