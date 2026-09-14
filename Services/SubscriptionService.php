<?php

namespace App\Modules\Shop\Services;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
use App\Modules\Shop\Tax\Tax;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Subscriptions and their renewals, mirroring the order flow: an order sells the
 * recurring lines, its payment creates the subscription; a renewal order is
 * generated when the next billing date comes, and its payment extends it.
 */
class SubscriptionService
{
    /** Called when an order is paid: creates the subscription (first order) or extends it (renewal). */
    public static function onOrderPaid(Order $order): ?Subscription
    {
        if ($order->isRenewal()) {
            return $order->subscription ? self::renewed($order->subscription, $order) : null;
        }

        if ($order->subscription_id) {
            return $order->subscription; // already created
        }

        return self::createFromOrder($order);
    }

    /** One subscription for the recurring lines of the order (monthly and yearly lines become separate subscriptions). */
    public static function createFromOrder(Order $order, ?Carbon $start = null): ?Subscription
    {
        $recurring = $order->items->filter(fn (OrderItem $item) => $item->isRecurring());
        if ($recurring->isEmpty()) {
            return null;
        }
        $start ??= now();
        $first = null;

        DB::transaction(function () use ($order, $recurring, $start, &$first) {
            foreach ($recurring->groupBy('period') as $period => $items) {
                $subscription = new Subscription([
                    'price_list_id'   => $order->price_list_id,
                    'order_id'        => $order->id,
                    'description'     => $items->pluck('name')->join(', '),
                    'period'          => $period,
                    'company_id'      => $order->company_id,
                    'user_id'         => $order->user_id,
                    'discount'        => 0, 'subtotal' => 0, 'tax' => 0, 'total' => 0,
                    'start_date'      => $start->toDateString(),
                    'status'          => 'active',
                    'managed_by'      => config('shop.subscriptions.managed_by', 'shop'),
                ]);
                $subscription->next_billing_at = $subscription->nextBillingAfter($start)->toDateString();
                $subscription->save();

                foreach ($items as $item) {
                    SubscriptionItem::create([
                        'subscription_id'    => $subscription->id,
                        'price_list_item_id' => $item->price_list_item_id,
                        'order_item_id'      => $item->id,
                        'name'               => $item->name,
                        'prd_code'           => $item->prd_code,
                        'bundle_code'        => $item->bundle_code,
                        'period'             => $period,
                        'price'              => $item->price,
                        'qty'                => $item->qty,
                        'subtotal'           => $item->subtotal,
                        'taxRate'            => $item->taxRate,
                        'total'              => round($item->subtotal * (1 + $item->taxRate / 100), 2),
                    ]);
                }
                $subscription->recalculate();

                $first ??= $subscription;
                if (! $order->subscription_id) {
                    $order->subscription_id = $subscription->id;
                    $order->save();
                }
            }
        });

        return $first?->fresh();
    }

    /** A renewal order for the next period, to be paid like any order. */
    public static function renew(Subscription $subscription): Order
    {
        $estimate = $subscription->company ? Tax::forCompany($subscription->company) : Tax::forUser($subscription->user);

        return DB::transaction(function () use ($subscription, $estimate) {
            $order = new Order();
            $order->id = (string) \Illuminate\Support\Str::uuid();
            $order->kind = 'renewal';
            $order->subscription_id = $subscription->id;
            $order->user_id = $subscription->user_id;
            $order->company_id = $subscription->company_id;
            $order->price_list_id = $subscription->price_list_id;
            $order->discount = 0;
            $order->shipping = 0;
            $order->subtotal = round((float) $subscription->items->sum('subtotal'), 2);
            $order->tax_rate = $estimate->rate;
            $order->tax_reason = $estimate->reason;
            $order->tax_source = $estimate->source;
            $order->tax_final = false;
            $order->tax = round($order->subtotal * $estimate->rate / 100, 2);
            $order->total = round($order->subtotal + $order->tax, 2);
            $order->note = 'Renewal of subscription ' . $subscription->shortId . ' (' . $subscription->period . ') from ' . $subscription->next_billing_at?->format('Y-m-d');
            $order->status = 'pending_payment';
            $order->save();

            foreach ($subscription->items as $item) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'price_list_item_id' => $item->price_list_item_id,
                    'deliverable_type'   => $item->deliverable_type,
                    'prd_code'           => $item->prd_code,
                    'name'               => $item->name,
                    'qty'                => $item->qty,
                    'price'              => $item->price,
                    'subtotal'           => $item->subtotal,
                    'taxRate'            => $estimate->rate,
                    'period'             => $item->period,
                    'bundle_code'        => $item->bundle_code ?? 0,
                ]);
            }

            return $order->fresh();
        });
    }

    /** The renewal is paid: the subscription runs for one more period. */
    public static function renewed(Subscription $subscription, Order $order): Subscription
    {
        $from = $subscription->next_billing_at ?? now();
        $subscription->next_billing_at = $subscription->nextBillingAfter($from)->toDateString();
        $subscription->status = 'active';
        $subscription->save();

        return $subscription;
    }

    /** Renewal orders for every subscription due today: run daily (shop:renew-subscriptions). */
    public static function renewDue(?Carbon $on = null): array
    {
        $orders = [];
        foreach (Subscription::dueForRenewal($on)->get() as $subscription) {
            $orders[] = self::renew($subscription);
        }

        return $orders;
    }

    public static function cancel(Subscription $subscription, ?Carbon $endsAt = null): Subscription
    {
        $subscription->status = 'cancelled';
        $subscription->ends_at = ($endsAt ?? now())->toDateString();
        $subscription->save();

        return $subscription;
    }
}
