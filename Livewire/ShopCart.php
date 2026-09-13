<?php


namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\CartFacade as Cart;

use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Tax\Tax;
use Livewire\Component;

use Zofe\Rapyd\Traits\WithDataTable;


class ShopCart extends Component
{
    use WithDataTable;

    public $note;

    #[\Livewire\Attributes\On('savedAddress')]
    public function onAddressSaved(): void
    {
        // forza il re-render per rivalutare hasAnyAddresses()
    }

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
        $order = OrderService::createOrderFromCart($this->note, auth()->user()->id);
        if ($order) {
            Cart::destroy();

            if (config('shop.checkout_mode', 'immediate') === 'immediate') {
                $workflow = \Workflow::get($order, 'order');
                if ($workflow->can($order, 'pay_order')) {
                    $workflow->apply($order, 'pay_order');
                    $order->save();
                }
            }

            session()->flash('success', 'Order created');
            return redirect()->route('shop.order', $order);
        }
    }

    public function render()
    {
        // The tax shown in the cart is the estimate for the logged-in customer
        $estimate = Tax::forUser(auth()->user());
        Cart::setGlobalTax($estimate->rate);

        $items = Cart::content();
        return view('shop::shop.shop_cart', compact('items', 'estimate'))->layout('shop::frontend');
    }
}
