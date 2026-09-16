<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\InventoryItem;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ServiceItem;
use App\Modules\Shop\Provisioning\Contracts\Provisioner;
use App\Modules\Shop\Provisioning\ProvisioningService;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Services\SubscriptionService;
use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder;

/**
 * What happens to sold goods: services get a ServiceItem + licence driven by a Provisioner
 * through the service_item workflow; physical goods are assigned, shipped and attributed.
 */
class ProvisioningTest extends TestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $this->seed(AuthSeeder::class);
        $this->user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $this->user->addresses()->create(['address' => 'Via Roma 1', 'city' => 'Bari', 'zipcode' => '70100', 'country_code' => 'IT']);
        config(['shop.tax_resolver' => 'flat', 'shop.tax' => 22]);
        $this->actingAs($this->user);
    }

    /** An order of the given price list items, paid (new → pending_payment → verification → payment_done). */
    protected function paidOrder(array $itemIds): Order
    {
        foreach ($itemIds as $id) {
            app('cart')->add(PriceListItem::find($id), [], 1);
        }
        $order = OrderService::createOrderFromCart(null, $this->user->id, null, $this->user->addresses()->first()->id);
        foreach (['pay_order', 'check_payment', 'payment_done'] as $transition) {
            $order->workflow_apply($transition, 'order');
            $order->save();
        }

        return $order->fresh();
    }

    public function test_a_paid_service_is_generated_licensed_and_provisioned()
    {
        $order = $this->paidOrder([5]);   // the extended warranty, a service bought once

        $assignment = $order->assignments()->first();
        $this->assertSame('generated', $assignment->status, 'the assignment moved through generate');

        $service = ServiceItem::find($assignment->deliverable_id);
        $this->assertSame('active', $service->status, 'provisioned by the default driver');
        $this->assertSame([$this->user->getMorphClass(), $this->user->id], [$service->owner_type, $service->owner_id]);
        $this->assertSame('order_item_assignment', $service->origin_type);
        $this->assertSame($order->id, $service->soldBy()->id);
        $this->assertArrayHasKey('provisioned_at', $service->metadata);

        $license = $service->license;
        $this->assertSame($license->id, $assignment->fresh()->license_id);
        $this->assertSame(now()->addMonths(12)->toDateString(), $license->expire_date->toDateString());
        $this->assertSame($this->user->id, $license->owner_id);

        // services only: no shipping step, the order completes right away
        $order->workflow_apply('process_order', 'order');
        $order->save();
        $this->assertFalse($order->workflow_can('ship_order', 'order'));
        $this->assertTrue($order->workflow_can('complete_order', 'order'));
    }

    public function test_physical_goods_are_assigned_shipped_then_owned_by_the_customer()
    {
        $order = $this->paidOrder([1]);   // the laptop: an inventory item with a serial
        $assignment = $order->assignments()->first();
        $this->assertSame('pending', $assignment->status, 'nothing automatic for physical goods');

        $order->workflow_apply('process_order', 'order');
        $order->save();
        $this->assertFalse($order->workflow_can('ship_order', 'order'), 'not before every unit is assigned');
        $this->assertFalse($order->workflow_can('complete_order', 'order'));

        $unit = InventoryItem::create(['product_id' => 1, 'status' => 'in_stock', 'serial_number' => 'SN-001']);
        $assignment->deliverable_id = $unit->id;
        $assignment->serial_number = $unit->serial_number;
        $assignment->workflow_apply('assign', 'order_item_assignment');
        $assignment->save();
        $unit->update(['status' => 'assigned']);

        $order = $order->fresh();
        $this->assertTrue($order->workflow_can('ship_order', 'order'));
        $this->assertFalse($order->workflow_can('complete_order', 'order'), 'ship first');

        $order->carrier = 'DHL';
        $order->tracking_code = 'JD0001';
        $order->workflow_apply('ship_order', 'order');
        $order->save();
        $this->assertTrue($order->workflow_can('complete_order', 'order'));

        $order->workflow_apply('complete_order', 'order');
        $order->save();

        $unit = $unit->fresh();
        $this->assertSame(['sold', $this->user->id], [$unit->status, $unit->owner_id], 'attributed to the customer when the order completes');
    }

    public function test_without_the_shipping_step_the_assignment_is_still_required()
    {
        config(['shop.provisioning.require_shipping' => false]);
        $order = $this->paidOrder([1]);
        $order->workflow_apply('process_order', 'order');
        $order->save();
        $this->assertFalse($order->workflow_can('complete_order', 'order'), 'assignment is still required');

        $unit = InventoryItem::create(['product_id' => 1, 'status' => 'in_stock', 'serial_number' => 'SN-002']);
        $assignment = $order->assignments()->first();
        $assignment->deliverable_id = $unit->id;
        $assignment->workflow_apply('assign', 'order_item_assignment');
        $assignment->save();
        $this->assertTrue($order->fresh()->workflow_can('complete_order', 'order'), 'no shipping step when it is not required');
    }

    public function test_a_custom_driver_is_chosen_per_product_and_called_by_the_workflow()
    {
        $driver = new class implements Provisioner {
            public array $calls = [];
            public function provision(ServiceItem $s): void { $this->calls[] = 'provision'; $s->external_ref = 'acct_42'; }
            public function suspend(ServiceItem $s): void { $this->calls[] = 'suspend'; }
            public function resume(ServiceItem $s): void { $this->calls[] = 'resume'; }
            public function terminate(ServiceItem $s): void { $this->calls[] = 'terminate'; if (($s->metadata['locked'] ?? false)) { throw new \RuntimeException('locked'); } }
        };
        config(['shop.provisioning.drivers' => ['acme' => $driver]]);
        Product::find(4)->update(['provisioner' => 'acme']);

        $order = $this->paidOrder([5]);
        $service = ServiceItem::find($order->assignments()->first()->deliverable_id);
        $this->assertSame(['provision'], $driver->calls);
        $this->assertSame('acct_42', $service->external_ref, 'what the driver stores is saved with the item');

        ProvisioningService::transition($service, 'suspend');
        ProvisioningService::transition($service->fresh(), 'resume');
        $this->assertSame(['provision', 'suspend', 'resume'], $driver->calls);

        // a driver that fails leaves the service where it was
        $service = $service->fresh();
        $service->metadata = ['locked' => true];
        $service->save();
        try {
            ProvisioningService::transition($service, 'terminate');
            $this->fail('the exception of the driver should abort the transition');
        } catch (\RuntimeException $e) {
        }
        $this->assertSame('active', $service->fresh()->status);
    }

    public function test_subscription_services_follow_the_subscription()
    {
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(7), 'monthly');
        $this->assertCount(0, ProvisioningService::servicesOf($subscription), 'nothing before the first payment');

        SubscriptionService::paymentConfirmed($subscription);
        $services = ProvisioningService::servicesOf($subscription->fresh());
        $this->assertCount(1, $services);
        $service = $services->first();
        $this->assertSame('active', $service->status);
        $this->assertSame('subscription_item', $service->origin_type);
        $this->assertSame($subscription->id, $service->soldBy()->id);
        $this->assertSame($subscription->fresh()->next_billing_at->toDateString(), $service->license->expire_date->toDateString(), 'the licence lasts until the next billing');
        $this->assertSame($service->id, $subscription->items()->first()->deliverable_id);

        SubscriptionService::paymentFailed($subscription->fresh());
        $this->assertSame('suspended', $service->fresh()->status);

        SubscriptionService::paymentConfirmed($subscription->fresh());
        $this->assertSame('active', $service->fresh()->status);
        $this->assertSame($subscription->fresh()->next_billing_at->toDateString(), $service->fresh()->license->expire_date->toDateString(), 'extended by the renewal');

        SubscriptionService::cancel($subscription->fresh());
        $this->assertSame('terminated', $service->fresh()->status);
    }

    public function test_a_trial_provisions_the_service_at_once()
    {
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(8), 'monthly');   // 14 days trial
        $service = ProvisioningService::servicesOf($subscription)->first();
        $this->assertSame('active', $service->status);
        $this->assertSame($subscription->trial_ends_at->toDateString(), $service->license->expire_date->toDateString());
    }
}
