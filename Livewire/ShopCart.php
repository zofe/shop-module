<?php


namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\CartFacade as Cart;

use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Tax\Tax;
use App\Modules\Shop\Tax\TaxContext;
use Livewire\Component;

use Zofe\Rapyd\Traits\WithDataTable;


class ShopCart extends Component
{
    use WithDataTable;

    public $note;

    /** The shipping address chosen in the list (remembered in the session). */
    public ?string $addressId = null;

    public function mount(): void
    {
        $this->addressId = session('shop.address_id');
    }

    #[\Livewire\Attributes\On('savedAddress')]
    public function onAddressSaved(): void
    {
        // forza il re-render per rivalutare hasAnyAddresses()
    }

    #[\Livewire\Attributes\On('selectedAddress')]
    public function onAddressSelected(?string $addressId = null): void
    {
        $this->addressId = $addressId;
        session(['shop.address_id' => $addressId]);
    }

    public function updateItem($rowId, $value)
    {
        Cart::update($rowId, $value);
    }

    public function removeItem($rowId)
    {
        Cart::remove($rowId);
    }

    /** Physical goods (inventory items) need a shipping address; services and licences do not. */
    public function requiresShipping(): bool
    {
        return Cart::content()->contains(fn ($item) => ($item->model?->product?->type ?? null) === 'inventory_item');
    }

    public function makeOrder()
    {
        if ($this->requiresShipping() && ! $this->addressId) {
            session()->flash('cart_error', 'Choose a shipping address before making the order.');
            return;
        }

        $order = OrderService::createOrderFromCart($this->note, auth()->user()->id, null, $this->addressId);
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
        // The tax shown in the cart is the estimate for the logged-in customer and the chosen address
        $addressable = auth()->user()?->company ?: auth()->user();
        $address = $this->addressId && $addressable ? $addressable->addresses()->find($this->addressId) : null;
        $requiresShipping = $this->requiresShipping();
        $estimate = Tax::forUser(auth()->user(), $requiresShipping ? TaxContext::PHYSICAL : TaxContext::DIGITAL, $address);
        Cart::setGlobalTax($estimate->rate);

        $items = Cart::content();
        return view('shop::shop.shop_cart', compact('items', 'estimate', 'requiresShipping'))->layout('shop::frontend');
    }
}
