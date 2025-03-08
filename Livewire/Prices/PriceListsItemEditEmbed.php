<?php

namespace App\Modules\Shop\Livewire\Prices;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\PriceList;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Product;
use Livewire\Component;



class PriceListsItemEditEmbed extends Component
{
    use Authorize;

    public $item;
    public $action = 'none';
    public $products = [];


    protected $listeners = [
        'editPriceListItem' => 'editPriceListItem',
        'deletePriceListItem' => 'deletePriceListItem',
        'addPriceListItem' => 'addPriceListItem',
        'toggle' => 'toggle',
    ];

    protected $rules = [
        'item.product_id' => 'required',
        'item.price_list_id' => 'required',
        'item.price_onetime_customer' => 'required',
        'item.price_monthly_customer' => 'nullable',
        'item.price_yearly_customer' => 'nullable',
    ];

    public function booted()
    {
        $this->authorize('admin|edit price lists');
    }

    public function mount(?PriceListItem $priceListItem)
    {
        $this->item = $priceListItem;
        if($this->item->exists){
            $this->action = 'show';
        }
    }


    public function save()
    {
        $this->validate();

        $this->item->save();
        $this->action = 'show';
    }

    public function delete()
    {
        $this->item->delete();
        $this->action = 'none';
        $this->dispatch('updatedPriceList');
    }

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
