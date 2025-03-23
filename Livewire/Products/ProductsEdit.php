<?php

namespace App\Modules\Shop\Livewire\Products;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductCategory;
use Illuminate\Support\Str;
use Livewire\Component;



class ProductsEdit extends Component
{
    use Authorize;

    public $product;
    public $availableCategories = [];

    public $types = [
        'inventory_item' => 'Inventory Item',
        'service_item' => 'Service Item',
    ];

    
    protected $rules = [
        'product.type' => 'required',
        'product.name' => 'required|unique:products,name',
        'product.description' => 'nullable|unique:companies,email',
        'product.sku' => 'required|unique:products,sku',
        'product.category_id' => 'required',
    ];

    public function booted()
    {
        $this->authorize('admin|edit products');
    }

    public function mount(?Product $product)
    {
        $this->product = $product;
        $this->availableCategories = ProductCategory::getNestedDropdown();

    }

    public function save()
    {
        if($this->product->exists) {
            $this->rules['product.name'] = 'required|unique:products,name,'.$this->product->id;
            $this->rules['product.sku'] = 'required|unique:products,sku,'.$this->product->id;
        }

        $this->validate();
        $this->product->slug = Str::slug($this->product->name);
        $this->product->save();
        return redirect()->to(route_lang("products.table"));
    }

    public function render()
    {
        return view('shop::products.products_edit')->layout('shop::admin');
    }
}
