<?php

namespace App\Modules\Shop\Provisioning;

use App\Modules\Shop\Models\InventoryItem;
use App\Modules\Shop\Models\License;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Models\ServiceItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Zofe\Rapyd\Modules\Workflow\Models\WorkflowStep;

/**
 * What happens to the goods once they are sold.
 *
 * Services: a ServiceItem per unit (from an order assignment or a subscription item) with an
 * owner, a licence and a driver (Provisioner); its `service_item` workflow (new → active →
 * suspended / terminated) calls the driver at every step. Physical goods: the inventory item
 * assigned by the operator, shipped, then attributed to the customer when the order completes.
 * Every step goes through the workflows, so an application can change guards and listeners.
 */
class ProvisioningService
{
    // ── Orders ─────────────────────────────────────────────────────────────

    /** The services of a paid order: a ServiceItem + licence per unit, provisioned by their driver. */
    public static function provisionOrder(Order $order): array
    {
        $services = [];
        foreach ($order->assignments()->where('order_items_assignments.deliverable_type', 'service_item')->get() as $assignment) {
            if ($assignment->status === 'pending') {
                $assignment->setRelation('orderItem', $assignment->orderItem->setRelation('order', $order));   // the order as it is now, not as saved
                $services[] = self::generateForAssignment($assignment);
            }
        }

        return $services;
    }

    /**
     * One unit of a service sold by an order: the record, the licence, the `generate` step; then the
     * driver (`provision`) right away when the product's activation policy is automatic, otherwise the
     * item waits in `new` for the operator or for the customer redeeming the key (see activate()).
     */
    public static function generateForAssignment(OrderItemAssignment $assignment, ?bool $provision = null): ServiceItem
    {
        return DB::transaction(function () use ($assignment, $provision) {
            $order = $assignment->orderItem->order;
            $product = $assignment->orderItem->priceListItem->product;
            $owner = self::customerOf($order);

            $priceItem = $assignment->orderItem->priceListItem;
            $service = ServiceItem::create([
                'product_id'  => $product->id,
                'status'      => 'new',
                'owner_type'  => $owner?->getMorphClass(),
                'owner_id'    => $owner?->getKey(),
                'origin_type' => 'order_item_assignment',
                'origin_id'   => $assignment->id,
                'provisioner' => $product->provisioner,
                'metadata'    => self::parametersOf($priceItem),
            ]);

            $months = (int) config('shop.provisioning.license_months', 12);
            $license = self::license($service, $owner, now()->addMonths($months)->toDateString(), $months);

            // the guard of `generate` wants an assignment without a deliverable: apply, then link
            $from = $assignment->workflow_get('order_item_assignment')->getMarking($assignment)->getPlaces();
            $assignment->workflow_apply('generate', 'order_item_assignment');
            $assignment->deliverable_type = 'service_item';
            $assignment->deliverable_id = $service->id;
            $assignment->license_id = $license->id;
            $assignment->save();
            self::step($assignment, 'generate', $from);

            $provision ??= $product->activationPolicy() === 'automatic';

            return $provision ? self::transition($service, 'provision') : $service;
        });
    }

    /**
     * Activate a generated service: the operator (manual policy) or the end user redeeming the licence
     * key (customer policy). The owner becomes $owner when given (the reseller sold it on).
     */
    public static function activate(ServiceItem $service, ?Model $owner = null): ServiceItem
    {
        if ($owner) {
            $service->owner_type = $owner->getMorphClass();
            $service->owner_id = $owner->getKey();
            $service->save();
            if ($license = $service->license) {
                $license->owner_type = $owner->getMorphClass();
                $license->owner_id = $owner->getKey();
                $license->save();
            }
        }

        return $service->status === 'new' ? self::transition($service, 'provision') : $service;
    }

    /** The service item a licence key belongs to, when the key exists and the item is still to activate. */
    public static function redeemable(string $key): ?ServiceItem
    {
        $license = License::where('key', $key)->first();
        $service = $license && $license->deliverable_type === 'service_item' ? ServiceItem::find($license->deliverable_id) : null;

        return $service && $service->status === 'new' ? $service : null;
    }

    /** The order is complete: the assigned inventory items belong to the customer. */
    public static function deliverOrder(Order $order): int
    {
        $owner = self::customerOf($order);
        $count = 0;
        foreach ($order->assignments()->where('order_items_assignments.deliverable_type', 'inventory_item')->whereNotNull('order_items_assignments.deliverable_id')->get() as $assignment) {
            if ($item = InventoryItem::find($assignment->deliverable_id)) {
                $item->owner_type = $owner?->getMorphClass();
                $item->owner_id = $owner?->getKey();
                $item->status = 'sold';
                $item->save();
                $count++;
            }
        }

        return $count;
    }

    // ── Subscriptions ──────────────────────────────────────────────────────

    /**
     * The subscription is active (paid, or on trial): every service item exists, is running and
     * its licence lasts until the next billing date.
     */
    public static function provisionSubscription(Subscription $subscription): array
    {
        $owner = self::customerOf($subscription);
        $until = ($subscription->next_billing_at ?? $subscription->trial_ends_at ?? now())->toDateString();
        $services = [];

        foreach ($subscription->items()->where('deliverable_type', 'service_item')->get() as $item) {
            $service = $item->deliverable_id ? ServiceItem::find($item->deliverable_id) : null;
            if (! $service) {
                $service = self::serviceForSubscriptionItem($item, $owner, $until);
            }
            if ($service->status === 'new') {
                $service = self::transition($service, 'provision');
            } elseif ($service->status === 'suspended') {
                $service = self::transition($service, 'resume');
            }
            if ($license = $service->license) {
                $license->expire_date = $until;
                $license->status = 'active';
                $license->save();
            }
            $services[] = $service;
        }

        return $services;
    }

    public static function suspendSubscription(Subscription $subscription): int
    {
        return self::transitionAll($subscription, 'suspend', ['active']);
    }

    public static function terminateSubscription(Subscription $subscription): int
    {
        return self::transitionAll($subscription, 'terminate', ['new', 'active', 'suspended']);
    }

    /** The service items of a subscription, through its items. */
    public static function servicesOf(Subscription $subscription)
    {
        return ServiceItem::where('origin_type', 'subscription_item')
            ->whereIn('origin_id', $subscription->items()->pluck('id'))
            ->get();
    }

    protected static function serviceForSubscriptionItem(SubscriptionItem $item, ?Model $owner, string $until): ServiceItem
    {
        return DB::transaction(function () use ($item, $owner, $until) {
            $product = $item->priceListItem?->product;
            $service = ServiceItem::create([
                'product_id'  => $product?->id,
                'status'      => 'new',
                'owner_type'  => $owner?->getMorphClass(),
                'owner_id'    => $owner?->getKey(),
                'origin_type' => 'subscription_item',
                'origin_id'   => $item->id,
                'provisioner' => $product?->provisioner,
                'metadata'    => self::parametersOf($item->priceListItem),
            ]);
            self::license($service, $owner, $until, null);
            $item->deliverable_type = 'service_item';
            $item->deliverable_id = $service->id;
            $item->saveQuietly();

            return $service;
        });
    }

    protected static function transitionAll(Subscription $subscription, string $transition, array $fromStatuses): int
    {
        $count = 0;
        foreach (self::servicesOf($subscription) as $service) {
            if (in_array($service->status, $fromStatuses, true) && $service->workflow_can($transition, 'service_item')) {
                self::transition($service, $transition);
                $count++;
            }
        }

        return $count;
    }

    // ── Service items ──────────────────────────────────────────────────────

    /** A step of the `service_item` workflow: the driver runs inside the transition, then the record is saved. */
    public static function transition(ServiceItem $service, string $transition, array $meta = []): ServiceItem
    {
        $from = $service->workflow_get('service_item')->getMarking($service)->getPlaces();
        $service->workflow_apply($transition, 'service_item');
        $service->save();
        self::step($service, $transition, $from, $meta);

        return $service->fresh();
    }

    public static function license(ServiceItem $service, ?Model $owner, ?string $expireDate, ?int $duration): License
    {
        return License::create([
            'product_id'       => $service->product_id,
            'deliverable_type' => 'service_item',
            'deliverable_id'   => $service->id,
            'key'              => License::generateKey(),
            'status'           => 'inactive',   // active once the service is provisioned
            'duration'         => $duration ?? 0,
            'activation_date'  => null,
            'expire_date'      => $expireDate,
            'owner_type'       => $owner?->getMorphClass(),
            'owner_id'         => $owner?->getKey(),
        ]);
    }

    /**
     * What the driver gets to know about the sold service: the parameters of the price list row
     * (metadata, e.g. devices: 5) and the attributes of the variant, if any. Null when there is nothing.
     */
    public static function parametersOf($priceItem): ?array
    {
        if (! $priceItem) {
            return null;
        }
        $params = array_merge($priceItem->variant?->metadata ?? [], $priceItem->metadata ?? []);

        return $params ?: null;
    }

    /** The customer of an order or a subscription: the company when there is one, else the user. */
    public static function customerOf(Model $origin): ?Model
    {
        return $origin->company ?? $origin->user;
    }

    protected static function step(Model $entity, string $transition, array $from, array $meta = []): void
    {
        WorkflowStep::create([
            'user_id'           => auth()->id(),
            'company_id'        => auth()->user()?->company_id,
            'workflowable_type' => $entity->getMorphClass(),
            'workflowable_id'   => $entity->getKey(),
            'places'            => $entity->workflow_get(null)->getMarking($entity)->getPlaces(),
            'places_from'       => $from,
            'last_transition'   => $transition,
            'transition_date'   => now(),
            'meta'              => $meta ?: null,
        ]);
    }
}
