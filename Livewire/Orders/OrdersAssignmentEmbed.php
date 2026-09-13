<?php

namespace App\Modules\Shop\Livewire\Orders;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Services\LicenseService;
use App\Modules\Shop\Services\ServicesService;
use Zofe\Rapyd\Modules\Workflow\Models\WorkflowStep;
use Livewire\Attributes\On;
use Livewire\Component;

class OrdersAssignmentEmbed extends Component
{
    use Authorize;

    public $assignment;

    protected $rules = [];

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    public function mount(string $assignmentId)
    {
        $this->assignment = OrderItemAssignment::findOrFail($assignmentId);
    }

    #[On('savedStep')]
    public function refreshAssignment(): void
    {
        $this->assignment = $this->assignment->fresh();
    }

    #[On('generateServiceAndLicense')]
    public function generateServiceAndLicense($morphableType, $morphableId)
    {
        if ($this->assignment->id == $morphableId) {
            $workflow   = \Workflow::get($this->assignment, 'order_item_assignment');
            $fromPlaces = $workflow->getMarking($this->assignment)->getPlaces();

            // transition must be applied before setting deliverable_id — guard blocks if it is already set
            $workflow->apply($this->assignment, 'generate');

            $product = $this->assignment->orderItem->priceListItem->product;
            $service = ServicesService::createServiceItemFromProduct($product);
            LicenseService::createLicenseFromProduct($product, 12);

            $this->assignment->deliverable_id   = $service->id;
            $this->assignment->deliverable_type = 'service_item';
            $this->assignment->save();

            WorkflowStep::create([
                'user_id'           => auth()->id(),
                'company_id'        => auth()->user()->company_id ?? null,
                'workflowable_type' => get_class($this->assignment),
                'workflowable_id'   => $this->assignment->getKey(),
                'places'            => $workflow->getMarking($this->assignment)->getPlaces(),
                'places_from'       => $fromPlaces,
                'last_transition'   => 'generate',
            ]);

            $this->assignment = $this->assignment->fresh();

            $this->dispatch('savedStep');
            $this->dispatch('refresh');
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
        if ($assignment->deliverable_type) {
            $view = "shop::orders.orders_assignment_{$assignment->deliverable_type}_embed";
        }

        return view($view, compact('assignment'))->layout('shop::admin');
    }
}
