<?php

namespace App\Modules\Shop\Livewire\Subscriptions;

use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Services\SubscriptionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Modules\Auth\Traits\Authorize;

class SubscriptionsView extends Component
{
    use Authorize;

    public Subscription $subscription;

    public function booted()
    {
        $this->authorize('admin|view subscriptions|edit subscriptions');
    }

    public function mount(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    #[On('savedStep')]
    #[On('refresh')]
    public function refresh(): void
    {
        $this->subscription->refresh();
    }

    /** A renewal order now, whatever the next billing date. */
    public function renewNow(): void
    {
        $this->authorize('admin|edit subscriptions');
        $order = SubscriptionService::renew($this->subscription);
        $this->redirect(route('orders.view', $order));
    }

    public function render()
    {
        $this->subscription->load(['items', 'order', 'renewals', 'user', 'company']);

        return view('shop::subscriptions.subscriptions_view', [
            'subscription' => $this->subscription,
            'payments'     => $this->subscription->payments(),
            'hasPayments'  => class_exists(\App\Modules\Payments\Models\Payment::class),
        ])->layout('shop::admin');
    }
}
