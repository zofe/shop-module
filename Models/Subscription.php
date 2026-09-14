<?php

namespace App\Modules\Shop\Models;

use App\Modules\Shop\Cart\Traits\RecalculatesTotals;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zofe\Rapyd\Modules\Workflow\Traits\WorkflowTrait;
use Zofe\Rapyd\Traits\SSearch;
use Zofe\Rapyd\Traits\ShortId;

/**
 * A recurring agreement with a customer: its items are the lines billed every
 * period. Created by the order that sold them; renewed by renewal orders (kind
 * "renewal") when the shop manages it, or mirrored from a gateway (managed_by
 * stripe, paddle…) that charges the customer itself.
 *
 * Payments (zofe/payments-module): the one that created it carries
 * subscription_id, every renewal carries ref_subscription_id.
 */
class Subscription extends Model
{
    use HasUuids, ShortId, RecalculatesTotals, SSearch, SoftDeletes, WorkflowTrait;

    public static array $searchableColumns = ['id', 'description', 'status'];

    protected $table = 'subscriptions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'price_list_id', 'order_id', 'description', 'period', 'company_id', 'user_id',
        'discount', 'subtotal', 'tax', 'total', 'start_date', 'next_billing_at', 'ends_at',
        'status', 'managed_by', 'gateway_ref',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'next_billing_at' => 'date',
        'ends_at'         => 'date',
    ];

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

    /** The order that created the subscription. */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** The renewal orders, newest first. */
    public function renewals()
    {
        return $this->hasMany(Order::class, 'subscription_id')->where('kind', 'renewal')->orderByDesc('created_at');
    }

    /** Payments of zofe/payments-module: the first one and the renewals; empty without the module. */
    public function payments()
    {
        if (! class_exists(\App\Modules\Payments\Models\Payment::class)) {
            return collect();
        }

        return \App\Modules\Payments\Models\Payment::query()
            ->where('subscription_id', $this->id)->orWhere('ref_subscription_id', $this->id)
            ->orderByDesc('created_at')->get();
    }

    public function isManagedByShop(): bool
    {
        return ($this->managed_by ?: 'shop') === 'shop';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'past_due']);
    }

    /** Due for a renewal order: managed by the shop, active, next billing reached, no pending renewal. */
    public function scopeDueForRenewal($query, ?Carbon $on = null)
    {
        $on ??= now();

        return $query->where('managed_by', 'shop')
            ->whereIn('status', ['active', 'past_due'])
            ->whereDate('next_billing_at', '<=', $on->toDateString())
            ->whereDoesntHave('renewals', fn ($q) => $q->whereIn('status', ['new', 'pending_payment', 'payment_verification']));
    }

    /** The next billing date after $from for this period. */
    public function nextBillingAfter(Carbon $from): Carbon
    {
        return $this->period === 'yearly' ? $from->copy()->addYear() : $from->copy()->addMonth();
    }
}
