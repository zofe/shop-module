<?php

namespace App\Modules\Shop\Livewire\Prices;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\PriceList;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class PriceListsItemEditEmbed extends Component
{
    use Authorize;

    public $item;
    public $action = 'none';
    public $products = [];
    public $metadata = [];

    protected $rules = [
        'item.product_id' => 'required',
        'item.price_list_id' => 'required',
        'item.product_variant_id'  => 'nullable',
        'item.has_onetime_payment' => 'boolean',
        'item.price_onetime'       => 'nullable|numeric|min:0',
        'item.has_activation_price' => 'boolean',
        'item.price_activation'    => 'nullable|numeric|min:0',
        'item.fee_canbe_monthly'   => 'boolean',
        'item.fee_monthly'         => 'nullable|numeric|min:0',
        'item.fee_canbe_yearly'    => 'boolean',
        'item.fee_yearly'          => 'nullable|numeric|min:0',
        'item.trial_days'          => 'nullable|integer|min:0',
        'metadata'    => 'nullable|array',
        'metadata.*'  => 'nullable|string',
    ];

    public function booted()
    {
        $this->authorize('admin|edit price lists');
    }

    public function mount(?PriceListItem $priceListItem)
    {
        $this->item = $priceListItem;
        $this->metadata = $this->item->metadata ?? [];   // key => value, edited by x-rpd::metadata
        if($this->item->exists){
            $this->action = 'show';
        }
    }


    public function save()
    {
        $this->validate();

        $this->item->metadata = \App\Modules\Shop\Livewire\Products\ProductsVariantsTableEmbed::cleanMetadata($this->metadata);
        $this->item->save();
        $this->action = 'show';
    }

    public function delete()
    {
        $this->item->delete();
        $this->action = 'none';
        $this->dispatch('updatedPriceList');
    }

    #[On('toggle')]
    public function toggle()
    {
        if($this->action == 'show' ) {
            $this->action = 'edit';
        } elseif($this->action == 'edit') {
            $this->item->refresh();
            $this->action = 'show';
        } elseif($this->action == 'create') {
            $this->action = 'none';
        }
    }

    #[On('addPriceListItem')]
    public function addPriceListItem($priceListId)
    {
        $priceList = PriceList::findOrFail($priceListId);

        if(!$this->item->exists){
            $this->item = new PriceListItem;
            $this->item->price_list_id = $priceList->id;
            $this->action = 'create';
        }
    }

    /** The variants of the chosen product, for the variant select. */
    public function variantsOf($productId): array
    {
        return $productId ? \App\Modules\Shop\Models\ProductVariant::where('product_id', $productId)->pluck('name', 'id')->toArray() : [];
    }

    public function render()
    {
        $this->products = Product::all()->pluck('name', 'id')->toArray();
        return view('shop::prices.price_lists_item_edit_embed');
    }
}
