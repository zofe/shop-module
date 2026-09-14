<?php

namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\Models\Subscription;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

/** The customer's subscriptions. */
class ShopSubscriptions extends Component
{
    use WithDataTable;

    public function mount()
    {
        if (auth()->guest()) {
            abort(403);
        }
        $this->sortField = 'created_at';
        $this->sortAsc = false;
    }

    public function render()
    {
        $items = Subscription::where('user_id', auth()->id())
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        [, , $categories] = \App\Modules\Shop\Services\ShopService::getContextBySlugs(null);

        return view('shop::shop.shop_subscriptions', compact('items', 'categories'))->layout('shop::frontend');
    }
}
