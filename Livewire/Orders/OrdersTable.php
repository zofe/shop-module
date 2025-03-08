<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class OrdersTable extends Component
{
    use WithDataTable, Authorize;

    public $search = '';
    public $sortField = 'id';

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function getDataSet()
    {
        $items = Order::orWhere('id', 'like', '%' . $this->search . '%');

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::orders.orders_table', compact('items'))->layout('shop::admin');
    }
}
