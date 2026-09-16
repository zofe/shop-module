<?php

namespace App\Modules\Shop\Livewire\Items;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;

use App\Modules\Shop\Models\ServiceItem;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class ServiceItemsTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';

    public function mount(): void
    {
        $this->sortField = 'id';
    }

    public function booted()
    {
        $this->authorize('admin|edit service items|view service items');
    }

    public function getDataSet()
    {
        $items = ServiceItem::with(['product', 'owner', 'license', 'origin'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id', 'like', '%' . $this->search . '%')
                ->orWhere('status', 'like', '%' . $this->search . '%')
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%'))));

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
