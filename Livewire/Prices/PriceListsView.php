<?php

namespace App\Modules\Shop\Livewire\Prices;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\PriceList;
use Livewire\Component;



class PriceListsView extends Component
{
    use Authorize;

    public $priceList;

    protected $listeners = ['updatedPriceList' => '$refresh'];

    public function booted()
    {
        $this->authorize('admin|edit prices|view prices');
    }

    public function mount(PriceList $priceList)
    {
        $this->priceList = $priceList;
    }

    public function render()
    {
        $priceList = $this->priceList;
        return view('shop::prices.price_lists_view', compact('priceList'))->layout('shop::admin');
    }
}
