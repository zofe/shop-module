<?php

namespace App\Modules\Shop\Livewire\Items;

use App\Modules\Shop\Models\ServiceItem;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Modules\Auth\Traits\Authorize;

/** A sold service: owner, origin, driver, licence, and its workflow (suspend / resume / terminate). */
class ServiceItemsEdit extends Component
{
    use Authorize;

    public $item;

    public function booted()
    {
        $this->authorize('admin|edit service items|view service items');
    }

    public function mount(?ServiceItem $item)
    {
        $this->item = $item;
    }

    #[On('refresh')]
    public function refresh(): void
    {
        $this->item = $this->item->fresh();
    }

    public function render()
    {
        return view('shop::items.service_items_edit')->layout('shop::admin');
    }
}
