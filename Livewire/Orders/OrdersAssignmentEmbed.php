<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\License;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Services\LicenseService;
use App\Modules\Shop\Services\ServicesService;
use Livewire\Attributes\On;
use Livewire\Component;



class OrdersAssignmentEmbed extends Component
{
    use Authorize;

    public $assignment;
    public $editable = false;


    protected $rules = [
    ];

    protected $listeners = [
    ];

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function mount(string $assignmentId)
    {
        $this->assignment = OrderItemAssignment::findOrFail($assignmentId);
    }


    #[On('editItem')]
    public function assignItem($morphableType, $morphableId)
    {

        if ($this->assignment->id == $morphableId) {
            $this->editable = true;
        }
    }

    #[On('generateServiceAndLicense')]
    public function generateServiceAndLicense($morphableType, $morphableId)
    {
        if ($this->assignment->id == $morphableId) {
            $this->editable = true;
            $product = $this->assignment->orderItem->priceListItem->product;
            $service = ServicesService::createServiceItemFromProduct($product);
            $license = LicenseService::createLicenseFromProduct($product, 12); //todo metadato dell'ordine

            $this->assignment->deliverable_id = $service->id;
            $this->assignment->deliverable_type = 'service_item';
            $this->assignment->save();
            //todo dovrei popolare il deliverable_id (ma con licenza o servizio?)
        }
    }


    public function debug()
    {
        $this->assignment->status = ($this->assignment->status == 'debug') ? 'pending' : 'debug';
        $this->assignment->save();
    }

    public function render()
    {
        $assignment = $this->assignment;

        $view = "shop::orders.orders_assignment_embed";
        if($assignment->deliverable_type) {
            $view = "shop::orders.orders_assignment_{$assignment->deliverable_type}_embed";
        }

        return view($view, compact('assignment'))->layout('shop::admin');
    }
}
