<?php

namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Payments\Contracts\PaymentRecorder;
use App\Modules\Shop\Payments\PaymentMethods;
use App\Modules\Shop\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

/** The customer's subscription: items (add / remove fees), the payments, pay the pending one. */
class ShopSubscription extends Component
{
    public Subscription $subscription;

    public function booted(): void
    {
        if (auth()->guest() || $this->subscription->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function mount(Subscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    #[On('savedStep')]
    public function refresh(): void
    {
        $this->subscription->refresh();
    }

    public function pay(string $key): void
    {
        $method = app(PaymentMethods::class)->find($key);
        $this->subscription->firstPeriod = $this->subscription->status === 'pending';   // the activation is part of the first period
        $pending = app(PaymentRecorder::class)->findPending($this->subscription);
        if (! $method || ! $method->available($this->subscription)) {
            return;
        }

        try {
            $start = $method->start($this->subscription, $pending);
        } catch (\RuntimeException $e) {
            session()->flash('checkout_message', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('subscription checkout: ' . $e->getMessage(), ['method' => $key, 'subscription_id' => $this->subscription->id, 'exception' => $e]);
            session()->flash('checkout_message', 'Payment could not be started. Please try again.');
            return;
        }

        if ($start->url) {
            $this->redirect($start->url);
            return;
        }
        $this->subscription->refresh();
        session()->flash('checkout_message', $start->message);
    }

    public function render()
    {
        $this->subscription->load('items');
        $recorder = app(PaymentRecorder::class);
        $this->subscription->firstPeriod = $this->subscription->status === 'pending';   // the activation is part of the first period
        $pending = $recorder->findPending($this->subscription);

        return view('shop::shop.shop_subscription', [
            'subscription' => $this->subscription,
            'pending'      => $pending,
            'payments'     => $recorder->history($this->subscription),
            'hasRecorder'  => $recorder->available(),
            'methods'      => $pending || ! $recorder->available() ? app(PaymentMethods::class)->for($this->subscription) : collect(),
        ])->layout('shop::frontend');
    }
}
