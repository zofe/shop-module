<?php

namespace App\Modules\Shop\Livewire\Orders;

use App\Modules\Shop\Models\Order;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use Zofe\Rapyd\Modules\Workflow\Models\WorkflowStep;

/** The "ship order" transition: carrier and tracking code, then the order is shipped. */
class OrdersShipModal extends Component
{
    use Authorize;

    public ?string $orderId = null;

    public ?string $carrier = null;

    public ?string $trackingCode = null;

    public ?string $document = null;   // DDT / delivery note reference

    public ?string $errorMessage = null;

    protected $rules = [
        'carrier'      => 'nullable|string|max:60',
        'trackingCode' => 'nullable|string|max:120',
        'document'     => 'nullable|string|max:120',
    ];

    public function booted()
    {
        $this->authorize('admin|edit orders');
    }

    #[On('shipOrder')]
    public function openModal($morphableType, $morphableId): void
    {
        $this->orderId = $morphableId;
        $this->carrier = null;
        $this->trackingCode = null;
        $this->document = null;
        $this->errorMessage = null;
        $this->dispatch('show-modal', ['shipOrder']);
    }

    public function doShip(): void
    {
        $this->validate();

        $order = Order::findOrFail($this->orderId);
        $workflow = \Workflow::get($order, 'order');
        if (! $workflow->can($order, 'ship_order')) {
            $this->errorMessage = 'The order cannot be shipped yet.';

            return;
        }
        $fromPlaces = $workflow->getMarking($order)->getPlaces();

        $order->carrier = $this->carrier;
        $order->tracking_code = $this->trackingCode;
        $order->shipping_document = $this->document;
        $order->shipped_at = now();
        $workflow->apply($order, 'ship_order');
        $order->save();

        WorkflowStep::create([
            'user_id'           => auth()->id(),
            'company_id'        => auth()->user()->company_id ?? null,
            'workflowable_type' => $order->getMorphClass(),
            'workflowable_id'   => $order->getKey(),
            'places'            => $workflow->getMarking($order)->getPlaces(),
            'places_from'       => $fromPlaces,
            'last_transition'   => 'ship_order',
            'transition_date'   => now(),
            'meta'              => array_filter(['carrier' => $this->carrier, 'tracking_code' => $this->trackingCode, 'document' => $this->document]),
        ]);

        $this->orderId = null;
        $this->dispatch('hide-modals');
        $this->dispatch('savedStep');
        $this->dispatch('refresh');
    }

    public function render()
    {
        return view('shop::orders.orders_ship_modal');
    }
}
