<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use Livewire\Component;



class OrdersView extends Component
{
    use Authorize;

    public $order;


    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        $order = $this->order;
        return view('shop::orders.orders_view', compact('order'))->layout('shop::admin');
    }
}
