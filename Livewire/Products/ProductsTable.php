<?php

namespace App\Modules\Shop\Livewire\Products;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Product;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class ProductsTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';

    public function booted()
    {
        $this->authorize('admin|edit products|view products');
    }

    public function mount(): void
    {
        $this->sortField = 'id';
    }

    public function getDataSet()
    {
        $items = Product::orWhere('id', 'like', '%' . $this->search . '%');

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::products.products_table', compact('items'))->layout('shop::admin');
    }
}
