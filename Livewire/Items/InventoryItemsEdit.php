<?php

namespace App\Modules\Shop\Livewire\Items;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\InventoryItem;
use Livewire\Component;



class InventoryItemsEdit extends Component
{
    use Authorize;

    public $item;

    protected $rules = [
        'item.serial_number' => 'required|unique:inventory_items,name',
        'item.product_id' => 'required',
    ];

    public function booted()
    {
        $this->authorize('admin|edit inventory items');
    }

    public function mount(?InventoryItem $item)
    {
       $this->item = $item;
    }

    public function save()
    {
        if($this->item->exists) {
            $this->rules['item.serial_number'] = 'required|unique:inventory_items,name,'.$this->item->id;
        }

        $this->validate();
        $this->item->save();
        return redirect()->to(route_lang("items.table"));
    }

    public function render()
    {
        return view('shop::items.inventory_items_edit')->layout('shop::admin');
    }
}
