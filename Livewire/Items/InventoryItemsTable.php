<?php

namespace App\Modules\Shop\Livewire\Items;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\InventoryItem;

use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class InventoryItemsTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';
    public $sortField = 'id';

    public function booted()
    {
        $this->authorize('admin|edit inventory items|view inventory items');
    }

    public function getDataSet()
    {
        $items = InventoryItem::orWhere('id', 'like', '%' . $this->search . '%');

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::items.inventory_items_table', compact('items'))->layout('shop::admin');
    }
}
