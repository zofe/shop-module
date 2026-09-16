<?php

namespace App\Modules\Shop\Livewire\Orders;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Provisioning\ProvisioningService;
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
            ProvisioningService::generateForAssignment($this->assignment);
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
