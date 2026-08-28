<?php

namespace App\Modules\Shop\Livewire\Prices;

use App\Modules\Auth\Traits\Authorize;
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
        'item.price_onetime_customer' => 'required',
        'item.price_monthly_customer' => 'nullable',
        'item.price_yearly_customer' => 'nullable',
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
        $this->metadata = collect($model->metadata ?? [])
            ->map(fn($value, $key) => ['key' => $key, 'value' => $value])
            ->values()
            ->toArray();
        if($this->item->exists){
            $this->action = 'show';
        }
    }


    public function save()
    {
        $this->validate();

        $clean = collect($this->metadata)
            ->filter(fn($value, $key) => trim((string)$key) !== '')
            ->toArray();
      //  $this->item->metadata = $clean;

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

    public function render()
    {
        $this->products = Product::all()->pluck('name', 'id')->toArray();
        return view('shop::prices.price_lists_item_edit_embed');
    }
}
