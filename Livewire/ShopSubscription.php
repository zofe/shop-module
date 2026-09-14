<?php

namespace App\Modules\Shop\Livewire;

use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
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

    public ?int $newItem = null;

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

    public function addItem(): void
    {
        $item = $this->newItem ? PriceListItem::find($this->newItem) : null;
        if ($item && $item->fee($this->subscription->period) && $this->subscription->isActive()) {
            SubscriptionService::addItem($this->subscription, $item);
            $this->newItem = null;
            $this->subscription->refresh();
            session()->flash('success', 'Added to the subscription from the next period');
        }
    }

    public function removeItem(int $itemId): void
    {
        $line = SubscriptionItem::where('subscription_id', $this->subscription->id)->find($itemId);
        if ($line && ! $line->bundle_code) {
            SubscriptionService::removeItem($line);
            $this->subscription->refresh();
        }
    }

    public function render()
    {
        $this->subscription->load('items');
        $recorder = app(PaymentRecorder::class);
        $this->subscription->firstPeriod = $this->subscription->status === 'pending';   // the activation is part of the first period
        $pending = $recorder->findPending($this->subscription);
        $list = \App\Modules\Shop\Models\PriceList::forCustomer(auth()->user());
        $addable = $list ? $list->items()->with('product')->get()->filter(fn ($i) => $i->fee($this->subscription->period))
            ->reject(fn ($i) => $this->subscription->items->contains('price_list_item_id', $i->id))
            ->mapWithKeys(fn ($i) => [$i->id => $i->name . ' — ' . number_format($i->fee($this->subscription->period), 2) . ' ' . \App\Modules\Shop\CartFacade::currency()])
            ->all() : [];

        return view('shop::shop.shop_subscription', [
            'subscription' => $this->subscription,
            'pending'      => $pending,
            'payments'     => $recorder->history($this->subscription),
            'hasRecorder'  => $recorder->available(),
            'methods'      => $pending || ! $recorder->available() ? app(PaymentMethods::class)->for($this->subscription) : collect(),
            'addable'      => $addable,
        ])->layout('shop::frontend');
    }
}
