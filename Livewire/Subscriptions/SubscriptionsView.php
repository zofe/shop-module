<?php

namespace App\Modules\Shop\Livewire\Subscriptions;

use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
use App\Modules\Shop\Payments\Contracts\PaymentRecorder;
use App\Modules\Shop\Services\SubscriptionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Modules\Auth\Traits\Authorize;

class SubscriptionsView extends Component
{
    use Authorize;

    public Subscription $subscription;

    public ?int $newItem = null;

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

    /** The operator saw the money: the pending payment is confirmed, the subscription moves on. */
    public function markPaid(string $paymentId): void
    {
        $this->authorize('admin|edit subscriptions');
        $recorder = app(PaymentRecorder::class);
        foreach ($recorder->history($this->subscription) as $payment) {
            if ((string) $payment->id === $paymentId && $payment->status === 'pending') {
                $recorder->confirm($payment, 'manual');
            }
        }
        $this->subscription->refresh();
    }

    public function markFailed(string $paymentId): void
    {
        $this->authorize('admin|edit subscriptions');
        $recorder = app(PaymentRecorder::class);
        foreach ($recorder->history($this->subscription) as $payment) {
            if ((string) $payment->id === $paymentId && $payment->status === 'pending') {
                $recorder->fail($payment, 'marked by operator');
                SubscriptionService::paymentFailed($this->subscription);
            }
        }
        $this->subscription->refresh();
    }

    /** Bill the next period now, whatever the date. */
    public function billNow(): void
    {
        $this->authorize('admin|edit subscriptions');
        SubscriptionService::billPeriod($this->subscription, $this->subscription->status === 'pending');
        $this->subscription->refresh();
    }

    public function addItem(): void
    {
        $this->authorize('admin|edit subscriptions');
        if ($this->newItem && ($item = PriceListItem::find($this->newItem)) && $item->fee($this->subscription->period)) {
            SubscriptionService::addItem($this->subscription, $item);
            $this->newItem = null;
            $this->subscription->refresh();
        }
    }

    public function removeItem(int $itemId): void
    {
        $this->authorize('admin|edit subscriptions');
        if ($line = SubscriptionItem::where('subscription_id', $this->subscription->id)->find($itemId)) {
            SubscriptionService::removeItem($line);
            $this->subscription->refresh();
        }
    }

    public function render()
    {
        $this->subscription->load(['items', 'user', 'company']);
        $recorder = app(PaymentRecorder::class);
        $addable = PriceListItem::with('product')->get()->filter(fn ($i) => $i->fee($this->subscription->period))
            ->mapWithKeys(fn ($i) => [$i->id => $i->name . ' — ' . number_format($i->fee($this->subscription->period), 2)])->all();

        return view('shop::subscriptions.subscriptions_view', [
            'subscription' => $this->subscription,
            'payments'     => $recorder->history($this->subscription),
            'hasRecorder'  => $recorder->available(),
            'pending'      => $recorder->findPending($this->subscription),
            'addable'      => $addable,
        ])->layout('shop::admin');
    }
}
