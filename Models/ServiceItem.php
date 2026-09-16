<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zofe\Rapyd\Modules\Workflow\Traits\WorkflowTrait;
use Zofe\Rapyd\Traits\ShortId;

/**
 * One unit of a sold service: who owns it, where it comes from (an order assignment or a
 * subscription item), which driver provisions it. Its status is the `service_item` workflow.
 */
class ServiceItem extends Model
{
    use HasUuids, ShortId, SoftDeletes, WorkflowTrait;

    protected $table = 'service_items';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    /** OrderItemAssignment or SubscriptionItem */
    public function origin()
    {
        return $this->morphTo();
    }

    public function license()
    {
        return $this->morphOne(License::class, 'deliverable')->latest('created_at');
    }

    /** The driver's name: the item's own, else the product's, else the default. */
    public function provisionerName(): string
    {
        return $this->provisioner ?: ($this->product?->provisioner ?: 'default');
    }

    /** The order or the subscription this service was sold by. */
    public function soldBy(): ?Model
    {
        $origin = $this->origin;

        return match (true) {
            $origin instanceof OrderItemAssignment => $origin->orderItem?->order,
            $origin instanceof SubscriptionItem => $origin->subscription,
            default => null,
        };
    }
}
