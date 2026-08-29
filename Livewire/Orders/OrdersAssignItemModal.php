<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\InventoryItem;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Workflow\Models\WorkflowStep;
use Livewire\Attributes\On;
use Livewire\Component;

class OrdersAssignItemModal extends Component
{
    use Authorize;

    public ?string $assignmentId           = null;
    public ?string $selectedInventoryItemId = null;
    public ?string $errorMessage           = null;

    protected $rules = [
        'selectedInventoryItemId' => 'required|string',
    ];

    public function booted()
    {
        $this->authorize('admin|edit orders|view orders');
    }

    #[On('assignItem')]
    public function openModal($morphableType, $morphableId): void
    {
        $this->assignmentId            = $morphableId;
        $this->selectedInventoryItemId = null;
        $this->errorMessage            = null;
        $this->dispatch('show-modal', ['assignItem']);
    }

    public function doAssign(): void
    {
        $this->validate();

        $assignment = OrderItemAssignment::findOrFail($this->assignmentId);

        $inventoryItem = InventoryItem::where('id', $this->selectedInventoryItemId)
            ->where('product_id', $assignment->orderItem->priceListItem->product_id)
            ->where('status', 'in_stock')
            ->first();

        if (! $inventoryItem) {
            $this->errorMessage = 'Item not found or not available for this product.';
            return;
        }

        $workflow   = \Workflow::get($assignment, 'order_item_assignment');
        $fromPlaces = $workflow->getMarking($assignment)->getPlaces();

        // deliverable_id must be set in memory before apply() — the guard reads it
        $assignment->deliverable_id = $inventoryItem->id;
        $assignment->serial_number  = $inventoryItem->serial_number;

        $workflow->apply($assignment, 'assign');
        $assignment->save();

        $inventoryItem->status = 'assigned';
        $inventoryItem->save();

        WorkflowStep::create([
            'user_id'           => auth()->id(),
            'company_id'        => auth()->user()->company_id ?? null,
            'workflowable_type' => get_class($assignment),
            'workflowable_id'   => $assignment->getKey(),
            'places'            => $workflow->getMarking($assignment)->getPlaces(),
            'places_from'       => $fromPlaces,
            'last_transition'   => 'assign',
        ]);

        $this->assignmentId            = null;
        $this->selectedInventoryItemId = null;
        $this->errorMessage            = null;

        $this->dispatch('hide-modals');
        $this->dispatch('savedStep');
        $this->dispatch('refresh');
    }

    public function render()
    {
        return view('shop::orders.orders_assign_item_modal');
    }
}
