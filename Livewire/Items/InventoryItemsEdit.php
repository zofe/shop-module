<?php

namespace App\Modules\Shop\Livewire\Items;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\InventoryItem;
use App\Modules\Shop\Models\Product;
use Livewire\Component;



class InventoryItemsEdit extends Component
{
    use Authorize;

    public $item;
    public $products = [];

    protected $rules = [
        'item.serial_number' => 'required|unique:inventory_items,serial_number',
        'item.product_id' => 'required',
    ];

    public function booted()
    {
        $this->authorize('admin|edit inventory items');
    }

    public function mount(?InventoryItem $item)
    {
       $this->item = $item;
       $this->products = Product::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function save()
    {
        if($this->item->exists) {
            $this->rules['item.serial_number'] = 'required|unique:inventory_items,serial_number,'.$this->item->id;
        }

        $this->validate();
        $this->item->save();
        return redirect()->to(route_lang("inventory_items.table"));
    }

    public function render()
    {
        return view('shop::items.inventory_items_edit')->layout('shop::admin');
    }
}
