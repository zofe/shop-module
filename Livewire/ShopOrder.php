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
        // own orders only: the customer, or their company
        $user = auth()->user();
        $mine = $this->order->user_id === $user->id || ($this->order->company_id && $this->order->company_id === ($user->company_id ?? null));
        abort_unless($mine || $user->hasRoleOrPermission('admin|view orders'), 403);
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
