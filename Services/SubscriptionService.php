<?php

namespace App\Modules\Shop\Services;

use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
use App\Modules\Shop\Payments\Contracts\PaymentRecorder;
use App\Modules\Shop\Tax\Tax;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The subscription flow, separate from the cart: "Subscribe" creates the
 * subscription with its items and the first pending payment; every period the
 * billing command creates the next pending payment; a confirmed payment
 * activates / extends the subscription, a failed one marks it past due.
 */
class SubscriptionService
{
    /** "Subscribe": a new subscription for a fee of the price list, first period pending (or a trial). */
    public static function subscribe(Model $user, PriceListItem $item, string $period, int $qty = 1, ?Model $address = null): Subscription
    {
        if (! $item->fee($period)) {
            throw new \InvalidArgumentException("createSubscription: {$item->name} is not sold {$period}");
        }
        $company = method_exists($user, 'company') ? $user->company : null;
        $estimate = $company ? Tax::forCompany($company, address: $address) : Tax::forUser($user, address: $address);
        $today = now();

        return DB::transaction(function () use ($user, $company, $item, $period, $qty, $estimate, $today) {
            $subscription = new Subscription([
                'price_list_id' => $item->price_list_id,
                'description'   => $item->name,
                'period'        => $period,
                'company_id'    => $company?->id,
                'user_id'       => $user->id,
                'discount'      => 0, 'subtotal' => 0, 'shipping' => 0, 'tax' => 0, 'total' => 0,
                'start_date'    => $today->toDateString(),
                'status'        => $item->trial_days > 0 ? 'trialing' : 'pending',
            ]);
            if ($item->trial_days > 0) {
                $subscription->trial_ends_at = $today->copy()->addDays($item->trial_days)->toDateString();
                $subscription->next_billing_at = $subscription->trial_ends_at;
            } else {
                $subscription->next_billing_at = $today->toDateString();   // the first period is due now
            }
            $subscription->save();

            self::addItem($subscription, $item, $qty, $estimate->rate);

            if (! $subscription->onTrial()) {
                self::billPeriod($subscription->fresh(), true);
            }

            return $subscription->fresh();
        });
    }

    /** A recurring line (a fee of the price list, same period as the subscription); components of a bundle at 0. */
    public static function addItem(Subscription $subscription, PriceListItem $item, int $qty = 1, ?float $taxRate = null): SubscriptionItem
    {
        $fee = $item->fee($subscription->period);
        if ($fee === null) {
            throw new \InvalidArgumentException("addItem: {$item->name} is not sold {$subscription->period}");
        }
        $taxRate ??= $subscription->items->first()?->taxRate ?? ($subscription->company ? Tax::forCompany($subscription->company) : Tax::forUser($subscription->user))->rate;

        $line = SubscriptionItem::create([
            'subscription_id'    => $subscription->id,
            'price_list_item_id' => $item->id,
            'product_variant_id' => $item->product_variant_id,
            'deliverable_type'   => $item->product->deliverableType(),
            'name'               => $item->name,
            'prd_code'           => $item->sku,
            'period'             => $subscription->period,
            'price'              => $fee,
            'qty'                => $qty,
            'subtotal'           => round($fee * $qty, 2),
            'taxRate'            => $taxRate,
            'total'              => round($fee * $qty * (1 + $taxRate / 100), 2),
        ]);

        if ($item->product->isBundle()) {
            foreach ($item->product->bundleItems as $component) {
                SubscriptionItem::create([
                    'subscription_id'    => $subscription->id,
                    'product_variant_id' => $component->product_variant_id,
                    'deliverable_type'   => $component->product->type,
                    'name'               => $component->name(),
                    'prd_code'           => $component->sku(),
                    'period'             => $subscription->period,
                    'price'              => 0, 'qty' => $qty * $component->qty, 'subtotal' => 0,
                    'taxRate'            => $taxRate, 'total' => 0,
                    'bundle_code'        => $line->id,
                ]);
            }
        }
        $subscription->recalculate();

        return $line;
    }

    public static function removeItem(SubscriptionItem $item): void
    {
        $subscription = $item->subscription;
        SubscriptionItem::where('bundle_code', $item->id)->delete();
        $item->delete();
        $subscription->recalculate();
    }

    /** The pending payment of the period starting at next_billing_at (activation included on the first one). */
    public static function billPeriod(Subscription $subscription, bool $first = false): ?object
    {
        $subscription->firstPeriod = $first;

        return app(PaymentRecorder::class)->pending($subscription);
    }

    /** Every subscription whose billing date has come: a pending payment for the next period. */
    public static function billDue(?Carbon $on = null): array
    {
        $billed = [];
        foreach (Subscription::dueForBilling($on)->get() as $subscription) {
            $first = $subscription->status === 'trialing' || $subscription->status === 'pending';
            if ($payment = self::billPeriod($subscription, $first)) {
                $billed[] = [$subscription, $payment];
            }
            if ($subscription->status === 'trialing') {
                $subscription->status = 'pending'; // the trial is over: waiting for the first payment
                $subscription->save();
            }
        }

        return $billed;
    }

    /** A payment of this subscription was confirmed: (re)activate it and move the billing date one period on. */
    public static function paymentConfirmed(Subscription $subscription, ?object $payment = null): Subscription
    {
        $from = $subscription->next_billing_at ?? now();
        $subscription->next_billing_at = $subscription->nextBillingAfter($from)->toDateString();
        $subscription->status = 'active';
        if ($payment && ! $subscription->gateway && ($payment->gateway ?? null)) {
            $subscription->gateway = $payment->gateway;
        }
        $subscription->save();

        return $subscription;
    }

    /** A payment failed, or a pending one is older than the grace period. */
    public static function paymentFailed(Subscription $subscription): Subscription
    {
        if ($subscription->status === 'active') {
            $subscription->status = 'past_due';
            $subscription->save();
        }

        return $subscription;
    }

    /** Pending payments older than config('shop.subscriptions.grace_days') mark their subscription past due. */
    public static function markPastDue(?Carbon $on = null): int
    {
        $on ??= now();
        $grace = (int) config('shop.subscriptions.grace_days', 7);
        $count = 0;
        foreach (Subscription::where('status', 'active')->whereDate('next_billing_at', '<=', $on->copy()->subDays($grace)->toDateString())->get() as $subscription) {
            self::paymentFailed($subscription);
            $count++;
        }

        return $count;
    }

    public static function cancel(Subscription $subscription, ?Carbon $endsAt = null): Subscription
    {
        $subscription->status = 'cancelled';
        $subscription->ends_at = ($endsAt ?? now())->toDateString();
        $subscription->save();

        return $subscription;
    }
}
