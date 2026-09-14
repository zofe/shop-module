<?php


namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\ProductCategory;
use App\Modules\Shop\Services\ShopService;
use Livewire\Component;

use Zofe\Rapyd\Traits\WithDataTable;


class Shop extends Component
{
    use WithDataTable;

    public $search;
    public $category = null;
    public $price = null;
    public $isHome = false;
    public $categories = [];
    //public $breadcrumbs = [];


    public $listeners = ['addToCart'=>'addToCart'];

    public function mount($slugs = null)
    {
        [$this->price, $this->category, $this->categories] = ShopService::getContextBySlugs($slugs);

        if ($slugs && !$this->category) {
           return redirect()->to(route_lang('shop.list'));
        }

        $this->isHome = !$slugs;
    }


    /** $period: onetime | monthly | yearly, one of the periods the item is sold with. */
    public function addToCart(string $period = 'onetime')
    {
        if (! $this->price || ! array_key_exists($period, $this->price->periods())) {
            return;
        }
        Cart::add($this->price, ['period' => $period], 1);
        session()->flash('success', 'Added to the cart');
    }

    public function render()
    {
        $homeItems = $this->isHome ? PriceListItem::query()->paginate(12) : [];
        return view('shop::shop.shop', compact('homeItems'))->layout('shop::frontend');

    }
}
