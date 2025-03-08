<?php


namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\CartFacade as Cart;
use Livewire\Component;

use Zofe\Rapyd\Traits\WithDataTable;


class ShopCart extends Component
{
    use WithDataTable;

    public $listeners = [];

    public function updateItem($rowId, $value)
    {
        Cart::update($rowId, $value);
    }

    public function removeItem($rowId)
    {
        Cart::remove($rowId);
    }

    public function makeOrder()
    {
        Cart::destroy();
    }

    public function render()
    {
        $items = Cart::content();
        return view('shop::shop.shop_cart', compact('items'))->layout('shop::frontend');
    }
}
