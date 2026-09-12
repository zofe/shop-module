<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Shop\Models\Order;

class CustomerOrdersTable extends OrdersTable
{
    public function booted()
    {
        $this->authorize('view own orders|pay own orders');
    }

    public function getDataSet()
    {
        return Order::where('user_id', auth()->id())
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::orders.customer_orders_table', compact('items'))->layout('shop::frontend');
    }
}
