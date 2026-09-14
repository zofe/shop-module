<?php

namespace App\Modules\Shop\Models;

use App\Modules\Shop\Cart\Traits\RecalculatesTotals;
use App\Modules\Shop\Payments\Contracts\Payable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zofe\Rapyd\Modules\Workflow\Traits\WorkflowTrait;
use Zofe\Rapyd\Traits\SSearch;
use Zofe\Rapyd\Traits\ShortId;

/**
 * A recurring agreement with a customer, born from the "Subscribe" button (never
 * from an order): its items are the fees billed every period. Each period a
 * local payment record is created pending and confirmed by the gateway or an
 * operator: the first one carries subscription_id, the following ones
 * ref_subscription_id (the structure of uania-web).
 *
 * Status (workflow "subscription"): pending → active | trialing → active,
 * past_due, cancelled.
 */
class Subscription extends Model implements Payable
{
    use HasUuids, ShortId, RecalculatesTotals, SSearch, SoftDeletes, WorkflowTrait;

    public static array $searchableColumns = ['id', 'description', 'status'];

    protected $table = 'subscriptions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'price_list_id', 'description', 'period', 'company_id', 'user_id',
        'discount', 'subtotal', 'shipping', 'tax', 'total',
        'start_date', 'next_billing_at', 'trial_ends_at', 'ends_at',
        'status', 'gateway', 'gateway_ref',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'next_billing_at' => 'date',
        'trial_ends_at'   => 'date',
        'ends_at'         => 'date',
    ];

    /** True while the first period is being billed (payments carry subscription_id, then ref_subscription_id). */
    public bool $firstPeriod = false;

    protected function getItems()
    {
        return $this->items;
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function company()
    {
        return $this->belongsTo(\Zofe\Rapyd\Modules\Companies\Models\Company::class);
    }

    public function items()
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'past_due']);
    }

    public function onTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /** Due for the next payment: active or in trial, the billing date reached. */
    public function scopeDueForBilling($query, ?Carbon $on = null)
    {
        $on ??= now();

        return $query->whereIn('status', ['active', 'past_due', 'trialing'])
            ->whereNotNull('next_billing_at')
            ->whereDate('next_billing_at', '<=', $on->toDateString());
    }

    /** The next billing date after $from for this period. */
    public function nextBillingAfter(Carbon $from): Carbon
    {
        return $this->period === 'yearly' ? $from->copy()->addYear() : $from->copy()->addMonth();
    }

    /** The period a payment covers, e.g. "2026-10 → 2026-11". */
    public function periodLabel(?Carbon $from = null): string
    {
        $from ??= $this->next_billing_at ?? $this->start_date ?? now();

        return $from->format('Y-m-d') . ' → ' . $this->nextBillingAfter($from)->format('Y-m-d');
    }

    // ---- Payable: the period being billed -----------------------------------

    public function payableType(): string
    {
        return 'subscription';
    }

    public function payableId(): string
    {
        return (string) $this->id;
    }

    public function payableDescription(): string
    {
        return 'Subscription ' . $this->shortId . ' · ' . $this->period . ' fee ' . $this->periodLabel();
    }

    public function payableAmounts(): array
    {
        $activation = $this->firstPeriod ? $this->activationTotal() : 0.0;
        $rate = $this->taxRate();

        return [
            'discount' => (float) $this->discount,
            'subtotal' => round((float) $this->subtotal + $activation, 2),
            'shipping' => 0.0,
            'tax'      => round(((float) $this->subtotal + $activation) * $rate / 100, 2),
            'total'    => round(((float) $this->subtotal + $activation) * (1 + $rate / 100), 2),
        ];
    }

    public function payableItems(): array
    {
        $rate = $this->taxRate();
        $items = $this->items->map(fn ($item) => [
            'name'                 => $item->name . ' (' . $item->period . ')',
            'prd_code'             => $item->prd_code,
            'subscription_item_id' => $item->id,
            'price_list_item_id'   => $item->price_list_item_id,
            'deliverable_type'     => $item->deliverable_type,
            'qty'                  => (float) $item->qty,
            'price'                => (float) $item->price,
            'subtotal'             => (float) $item->subtotal,
            'shipping'             => 0.0,
            'discountRate'         => 0.0,
            'discount'             => 0.0,
            'taxRate'              => $rate,
            'tax'                  => round((float) $item->subtotal * $rate / 100, 2),
            'total'                => round((float) $item->subtotal * (1 + $rate / 100), 2),
        ])->all();

        if ($this->firstPeriod) {
            foreach ($this->items as $item) {
                if ($activation = $item->activationPrice()) {
                    $items[] = [
                        'name' => $item->name . ' — activation', 'prd_code' => $item->prd_code, 'subscription_item_id' => $item->id,
                        'qty' => (float) $item->qty, 'price' => $activation, 'subtotal' => round($activation * $item->qty, 2),
                        'shipping' => 0.0, 'discountRate' => 0.0, 'discount' => 0.0, 'taxRate' => $rate,
                        'tax' => round($activation * $item->qty * $rate / 100, 2), 'total' => round($activation * $item->qty * (1 + $rate / 100), 2),
                    ];
                }
            }
        }

        return $items;
    }

    public function payableUser()
    {
        return $this->user;
    }

    public function payableCompany()
    {
        return $this->company;
    }

    public function payableCustomerEmail(): ?string
    {
        return $this->user?->email;
    }

    public function payableLinks(): array
    {
        return $this->firstPeriod ? ['subscription_id' => $this->id] : ['ref_subscription_id' => $this->id];
    }

    public function activationTotal(): float
    {
        return round($this->items->sum(fn ($item) => $item->activationPrice() * $item->qty), 2);
    }

    /** The rate of the items (one estimate for the whole subscription). */
    public function taxRate(): float
    {
        $first = $this->items->first();

        return $first ? (float) $first->taxRate : (float) config('shop.tax', 22);
    }
}
