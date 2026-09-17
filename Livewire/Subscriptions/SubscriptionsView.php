<?php

namespace App\Modules\Shop\Livewire\Subscriptions;

use App\Modules\Shop\Models\PriceList;
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

    // ── the line modal: add a fee, or change the quantity / variant of a line ──
    public ?int $editingId = null;

    public ?int $lineProduct = null;    // product id (add)

    public ?int $lineVariant = null;    // variant id (add / edit)

    public int $lineQty = 1;

    public function openLine(?int $itemId = null): void
    {
        $this->authorize('admin|edit subscriptions');
        $this->resetErrorBag();
        $this->editingId = $itemId;
        $line = $itemId ? SubscriptionItem::where('subscription_id', $this->subscription->id)->find($itemId) : null;
        $this->lineProduct = $line?->priceListItem?->product_id;
        $this->lineVariant = $line?->product_variant_id;
        $this->lineQty = $line ? (int) $line->qty : 1;
        $this->dispatch('show-modal', ['subscriptionLine']);
    }

    public function saveLine(): void
    {
        $this->authorize('admin|edit subscriptions');
        $this->validate(['lineQty' => 'required|integer|min:1', 'lineProduct' => $this->editingId ? 'nullable' : 'required|integer']);

        if ($this->editingId) {
            if ($line = SubscriptionItem::where('subscription_id', $this->subscription->id)->find($this->editingId)) {
                SubscriptionService::updateItem($line, $this->lineQty, $this->lineVariant);
            }
        } else {
            $list = $this->subscription->priceList ?? PriceList::default();
            $item = $list?->itemFor($this->lineProduct, $this->lineVariant ?: null);
            if (! $item || $item->fee($this->subscription->period) === null) {
                $this->addError('lineProduct', 'This product has no ' . $this->subscription->period . ' fee in the price list.');

                return;
            }
            SubscriptionService::addItem($this->subscription, $item, $this->lineQty);
        }
        $this->subscription->refresh();
        $this->dispatch('hide-modals');
    }

    /** Products sold as a fee for this period (in the subscription's price list), for the modal. */
    public function lineProducts(): array
    {
        $list = $this->subscription->priceList ?? PriceList::default();

        return $list ? $list->items()->with('product')->get()->filter(fn ($i) => $i->fee($this->subscription->period) !== null)
            ->mapWithKeys(fn ($i) => [$i->product_id => $i->product->name])->all() : [];
    }

    /** Variants of the chosen product that have a fee for this period; [] when the product has none. */
    public function lineVariants(): array
    {
        if (! $this->lineProduct) {
            return [];
        }
        $list = $this->subscription->priceList ?? PriceList::default();

        return $list ? $list->items()->with('variant')->where('product_id', $this->lineProduct)->whereNotNull('product_variant_id')->get()
            ->filter(fn ($i) => $i->fee($this->subscription->period) !== null)
            ->mapWithKeys(fn ($i) => [$i->product_variant_id => $i->variant?->name . ' — ' . number_format($i->fee($this->subscription->period), 2)])->all() : [];
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
        return view('shop::subscriptions.subscriptions_view', [
            'subscription' => $this->subscription,
            'payments'     => $recorder->history($this->subscription),
            'hasRecorder'  => $recorder->available(),
            'pending'      => $recorder->findPending($this->subscription),
            'lineProducts' => $this->lineProducts(),
            'lineVariants' => $this->lineVariants(),
        ])->layout('shop::admin');
    }
}
