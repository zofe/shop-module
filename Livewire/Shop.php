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


    public function addToCart()
    {
        Cart::add($this->price, 1);
    }

    public function render()
    {
        $homeItems = $this->isHome ? PriceListItem::query()->paginate(12) : [];
        return view('shop::shop.shop', compact('homeItems'))->layout('shop::frontend');

    }
}
