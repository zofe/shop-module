<?php

namespace App\Modules\Shop\Livewire\Orders;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItem;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Services\OrderService;
use Livewire\Attributes\On;
use Livewire\Component;



class OrdersModalEditEmbed extends Component
{
    use Authorize;

    public $order;
    public $item;

    public $new_item;
    public $qty;
    public $price;
    public $shipping;
    public $prd_code;
    public $name;

    protected $rules = [
        'new_item'  => 'nullable',
        'price'     => 'required|numeric',
        'qty'       => 'required|integer|min:1|max:200',
        'prd_code'  => 'required',
        'shipping'  => 'nullable',
    ];

    public function updatedNewItem()
    {
        $item = PriceListItem::findOrFail($this->new_item);
        $this->qty = 1;
        $this->price = $item->getBuyablePrice();
        $this->shipping = $item->shipping?:0;
        $this->prd_code = $item->sku;
        $this->name =  $item->name;
    }

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    #[On('editItem')]
    #[On('editOrderItem')]
    public function editItem($itemId = null)
    {
        if ($itemId) {
            $item = OrderItem::find($itemId) ?: new OrderItem;

            //$this->bundle_license = $item->bundle_license;
            //$this->bundle_service = $item->bundle_service;
            //$this->bundle_box = $item->bundle_box;
            $this->qty = $item->qty;
            $this->price = $item->price;
            $this->shipping = $item->shipping;
            $this->prd_code = $item->prd_code;
            $this->name = $item->name;

            $this->item = $item;
        } else {
            $this->item = new OrderItem;
            $this->qty = 1;
            $this->price = 0;
            $this->shipping = 0;
            $this->prd_code = '';
            $this->name = '';
        }


        $this->dispatch('show-modal',['editItem']);
    }

    public function saveItem()
    {
        $this->item->order_id = $this->order->id;
        $this->validate();

        $this->item->updatePriceQty($this->price, $this->qty, $this->shipping, $this->prd_code, $this->name);

        OrderService::syncAssignments($this->item);
        $this->order->refresh();
        $this->dispatch('hide-modals');
        $this->dispatch('refresh');
    }

    public function render()
    {
        return view('shop::orders.orders_modal_edit_embed');
    }
}
