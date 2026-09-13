<?php


namespace App\Modules\Shop\Livewire;


use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Order;
use Livewire\Component;

use Zofe\Rapyd\Traits\WithDataTable;


class ShopOrders extends Component
{
    use WithDataTable, Authorize;

    public function booted()
    {
        $this->authorize('admin|edit own orders|view own orders');
    }

    public function mount()
    {
        $this->sortField = 'created_at';
        $this->sortAsc = false;
    }

    public function getDataSet()
    {
        $items = Order::where(function ($q)  {
            if(auth()->user()->company_id) {
                $q->where('company_id', auth()->user()->company_id);
            } else {
                $q->where('user_id', auth()->user()->id);
            }
        });

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();
        return view('shop::shop.shop_orders', compact('items'))->layout('shop::frontend');
    }
}
