<?php

namespace App\Modules\Shop\Livewire\Products;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Product;
use Livewire\Component;



class ProductsView extends Component
{
    use Authorize;

    public $product;

    public function booted()
    {
        $this->authorize('admin|edit products|view products');
    }

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function render()
    {
        $product = $this->product;
        return view('shop::products.products_view', compact('product'))->layout('shop::admin');
    }
}
