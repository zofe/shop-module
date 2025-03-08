<?php

namespace App\Modules\Shop\Livewire\Prices;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\PriceList;
use Livewire\Component;



class PriceListsModalEditEmbed extends Component
{
    use Authorize;

    public $priceList;

    protected $listeners = [
        'editPriceList' => 'editPriceList',
        'deletePriceList' => 'deletePriceList'
    ];

    protected $rules = [
        'priceList.name' => 'required',
        'priceList.is_active' => 'boolean',
        'priceList.is_default' => 'boolean',

    ];

    public function booted()
    {
        $this->authorize('admin|edit price lists');
    }

    public function editPriceList($priceListId = null)
    {
        if ($priceListId) {
            $this->priceList = PriceList::find($priceListId) ?: new PriceList;
        } else {
            $this->priceList = new PriceList;
            $this->priceList->is_active = false;
            $this->priceList->is_default = false;
        }

        $this->dispatch('show-modal',['editPriceList']);
    }

    public function save()
    {
        $this->validate([
            'priceList.name' => 'required|string|max:255',
            'priceList.is_active' => 'boolean',
            'priceList.is_default' => 'boolean',
        ]);

        $this->priceList->save();
        $this->dispatch('hide-modals');
        $this->dispatch('savedPriceList');
    }

    public function deletePriceList($priceListId)
    {
        $this->priceList = PriceList::findOrfail($priceListId);
        $this->priceList->delete();
        $this->dispatch('savedPriceList');
    }


    public function render()
    {
        return view('shop::prices.price_lists_modal_edit_embed');
    }
}
