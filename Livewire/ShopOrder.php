<?php


namespace App\Modules\Shop\Livewire;


use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use Livewire\Component;


class ShopOrder extends Component
{
    use Authorize;

    public $order;

    public function booted()
    {
        $this->authorize('admin|edit own orders|view own orders');
    }

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function render()
    {
        $order = $this->order;
        return view('shop::shop.shop_order', compact('order'))->layout('shop::frontend');
    }
}
