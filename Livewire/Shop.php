<?php


namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\ProductCategory;
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
    public $breadcrumbs = [];


    public $listeners = ['addToCart'=>'addToCart'];

    public function mount($slugs = null)
    {
        if ($slugs)
        {
            $slugParts = explode('/', $slugs);
            $slug = end($slugParts);

            $this->price = PriceListItem::whereHas('product', function($query) use ($slug) {
                $query->where('slug', $slug);
            })->first();

            if(!$this->price) {
                $this->category = ProductCategory::where('slug', $slug)->first();
                if(!$this->category || $this->category->full_path != $slugs) {
                    return redirect()->to(route_lang('shop.list'));
                }
            } else {
                $this->category = $this->price->product->category;
            }

            $this->categories = optional($this->category)->children ?? [];
            $this->breadcrumbs = $this->buildBreadcrumbs($this->category);

        } else {
            $this->categories = ProductCategory::whereNull('parent_id')->orderBy('order')->get();
            $this->isHome = true ;
        }
    }


    protected function buildBreadcrumbs(ProductCategory $category)
    {
        $breadcrumbs = [];
        while ($category) {
            $breadcrumbs[] = $category;
            $category = $category->parent;
        }
        return array_reverse($breadcrumbs);
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
