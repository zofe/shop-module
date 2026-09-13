<?php

namespace App\Modules\Shop\Livewire\Items;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\ServiceItem;
use Livewire\Component;



class ServiceItemsEdit extends Component
{
    use Authorize;

    public $item;

    protected $rules = [
       // 'item.product_id' => 'required',
    ];

    public function booted()
    {
        $this->authorize('admin|edit service items');
    }

    public function mount(?ServiceItem $item)
    {
       $this->item = $item;
    }

    public function save()
    {
//        if($this->item->exists) {
//            $this->rules['item.serial_number'] = 'required|unique:inventory_items,name,'.$this->item->id;
//        }
//
//        $this->validate();
        $this->item->save();
        return redirect()->to(route_lang("service_items.table"));
    }

    public function render()
    {
        return view('shop::items.service_items_edit')->layout('shop::admin');
    }
}
