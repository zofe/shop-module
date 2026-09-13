<?php

namespace App\Modules\Shop\Livewire\Products;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductVariant;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class ProductsVariantsTableEmbed extends Component
{
    use WithDataTable, Authorize;

    public $search = '';

    public $product;
    public $editable = false;
    public $variants;
    public $variant;
    public $metadata = [];

    protected $rules = [
        'variant.product_id' => 'required',
        'variant.name' => 'required',
        'variant.sku' => 'required',
        'metadata'    => 'nullable|array',
        'metadata.*'  => 'nullable|string',
    ];

    public function booted()
    {
        $this->authorize('admin|edit products');
    }

    public function mount(Product $product, $editable = false)
    {
        $this->sortField = 'id';

        $this->product = $product;
        $this->metadata = collect($product->metadata ?? [])
            ->map(fn($value, $key) => ['key' => $key, 'value' => $value])
            ->values()
            ->toArray();

        $this->editable = $editable;

        $this->refreshVariants();
    }

    #[On('savedVariant')]
    public function refreshVariants()
    {
        $this->variants = $this->product->variants()->get();
    }

    #[On('editVariant')]
    public function editVariant(ProductVariant $variant = null)
    {
        $this->variant = $variant ?: new ProductVariant;
        $this->variant->product_id = $this->product->id;
        $this->dispatch('show-modal',['editVariant']);
    }

    public function save()
    {
        $this->validate();
        $metadata = collect($this->metadata)
            ->filter(fn($value, $key) => trim((string)$key) !== '')
            ->toArray();
        $this->variant->metadata = $metadata;
        $this->variant->save();

        $this->dispatch('hide-modals');
        $this->dispatch('savedVariant');
    }

    #[On('deleteVariant')]
    public function deleteVariant(ProductVariant $variant)
    {
        $variant->delete();
        $this->dispatch('savedVariant');
    }


    public function render()
    {
        $variants = $this->variants;
        $variant = $this->variant;

        return view('shop::products.products_variants_table_embed', compact('variants','variant'))
            ->layout('shop::admin');
    }
}
