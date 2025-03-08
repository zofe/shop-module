<?php

namespace App\Modules\Shop\Livewire\Items;

use App\Modules\Auth\Traits\Authorize;

use App\Modules\Shop\Models\ServiceItem;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class ServiceItemsTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';
    public $sortField = 'id';

    public function booted()
    {
        $this->authorize('admin|edit service items|view service items');
    }

    public function getDataSet()
    {
        $items = ServiceItem::orWhere('id', 'like', '%' . $this->search . '%');

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::items.service_items_table', compact('items'))->layout('shop::admin');
    }
}
