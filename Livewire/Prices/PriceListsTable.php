<?php

namespace App\Modules\Shop\Livewire\Prices;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\PriceList;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class PriceListsTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';
    public $sortField = 'id';

    protected $listeners = ['savedPriceList' => '$refresh'];

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function getDataSet()
    {
        $items = PriceList::orWhere('id', 'like', '%' . $this->search . '%');

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::prices.price_lists_table', compact('items'))->layout('shop::admin');
    }
}
