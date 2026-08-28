<?php

namespace App\Modules\Shop\Livewire\Subscriptions;


use App\Modules\Shop\Models\Subscription;
use App\Modules\Auth\Traits\Authorize;

use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class SubscriptionsTable extends Component
{
    use WithDataTable;
    use Authorize;

    public $search;


    public function booted()
    {
        $this->authorize('admin|edit subscriptions|view subscriptions');
    }

    public function mount()
    {
        $this->sortAsc = false;
        $this->sortField = 'created_at';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getDataSet()
    {
        $items = Subscription::search($this->search)
        ;

        return $items = $items
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage)
            ;
    }

    public function render()
    {
        $items = $this->getDataSet();

        return view('shop::subscriptions.subscriptions_table', compact('items'))->layout('shop::admin');
    }
}
