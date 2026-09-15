<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\PriceList;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Services\SubscriptionService;
use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder;

/**
 * The subscription flow, separate from the cart. Without zofe/payments-module the
 * recorder is the NullRecorder: no payment records, the workflow carries the state.
 */
class SubscriptionsTest extends TestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $this->seed(AuthSeeder::class);   // roles: a registered customer gets "customer" (edit own users → their addresses)
        $this->user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $this->user->addresses()->create(['address' => 'Via Roma 1', 'city' => 'Bari', 'zipcode' => '70100', 'country_code' => 'IT']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web'])
            ->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edit own users', 'guard_name' => 'web']));
        $this->user->assignRole('customer');
        config(['shop.tax_resolver' => 'flat', 'shop.tax' => 22]);
    }

    public function test_the_price_list_says_how_a_product_is_sold()
    {
        $licence = PriceListItem::find(1);   // 5 users
        $this->assertTrue($licence->isPurchasable());
        $this->assertSame([], $licence->fees());
        $this->assertSame('Rapyd Admin — Professional License — 5 users', $licence->name);
        $this->assertSame('RPD-PRO-5', $licence->sku);
        $this->assertSame(499.0, PriceListItem::find(3)->getBuyablePrice(), 'the 20 users variant');

        $support = PriceListItem::find(2);
        $this->assertFalse($support->isPurchasable());
        $this->assertSame(['monthly' => 14.9, 'yearly' => 149.0], $support->fees());
        $this->assertSame(20.0, $support->activationPrice());

        $this->assertSame(14, PriceListItem::find(4)->trial_days);
        $this->assertTrue(PriceListItem::find(5)->product->isBundle());
        $this->assertSame(PriceListItem::find(1)->id, PriceList::default()->itemFor(1, 1)->id);
    }

    public function test_the_cart_sells_one_time_products_only()
    {
        app('cart')->add(PriceListItem::find(3), [], 1);
        $this->assertSame(499.0, (float) app('cart')->content()->first()->price);

        app('cart')->add(PriceListItem::find(2), [], 1);   // a fee: 0 in the cart, it is not a purchase
        $this->assertSame(0.0, (float) app('cart')->content()->last()->price);
    }

    public function test_subscribe_creates_the_subscription_with_its_items_pending_the_first_payment()
    {
        Carbon::setTestNow('2026-09-14');
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(2), 'monthly');

        $this->assertSame(['monthly', 'pending', '2026-09-14', '2026-09-14'], [
            $subscription->period, $subscription->status, $subscription->start_date->toDateString(), $subscription->next_billing_at->toDateString(),
        ]);
        $this->assertCount(1, $subscription->items);
        $item = $subscription->items->first();
        $this->assertSame(['RPD-SUP', 'monthly', 14.9, 1, 22.0], [$item->prd_code, $item->period, (float) $item->price, (int) $item->qty, (float) $item->taxRate]);
        $this->assertEqualsWithDelta(14.9 * 1.22, $subscription->total, 0.01);
        $this->assertSame(0, Order::where('user_id', $this->user->id)->count(), 'no order is involved');

        // the first period as a payable: the fee plus the activation
        $subscription->firstPeriod = true;
        $this->assertEqualsWithDelta((14.9 + 20) * 1.22, $subscription->payableAmounts()['total'], 0.01);
        $this->assertCount(2, $subscription->payableItems());
        $this->assertSame(['subscription_id' => $subscription->id], $subscription->payableLinks());
        $subscription->firstPeriod = false;
        $this->assertSame(['ref_subscription_id' => $subscription->id], $subscription->payableLinks());
        Carbon::setTestNow();
    }

    public function test_a_confirmed_payment_activates_and_extends_the_subscription()
    {
        Carbon::setTestNow('2026-09-14');
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(2), 'yearly');

        SubscriptionService::paymentConfirmed($subscription);
        $this->assertSame(['active', '2027-09-14'], [$subscription->status, $subscription->next_billing_at->toDateString()]);

        $this->assertSame(0, Subscription::dueForBilling(Carbon::parse('2027-09-13'))->count());
        $this->assertSame(1, Subscription::dueForBilling(Carbon::parse('2027-09-14'))->count());

        SubscriptionService::paymentConfirmed($subscription);
        $this->assertSame('2028-09-14', $subscription->fresh()->next_billing_at->toDateString());
        Carbon::setTestNow();
    }

    public function test_a_trial_starts_free_and_is_billed_at_its_end()
    {
        Carbon::setTestNow('2026-09-14');
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(4), 'monthly');
        $this->assertSame(['trialing', '2026-09-28', '2026-09-28'], [$subscription->status, $subscription->trial_ends_at->toDateString(), $subscription->next_billing_at->toDateString()]);

        Artisan::call('shop:bill-subscriptions', ['--date' => '2026-09-27']);
        $this->assertSame('trialing', $subscription->fresh()->status);

        Artisan::call('shop:bill-subscriptions', ['--date' => '2026-09-28']);
        $this->assertSame('pending', $subscription->fresh()->status, 'the trial is over, the first payment is due');

        SubscriptionService::paymentConfirmed($subscription->fresh());
        $this->assertSame(['active', '2026-10-28'], [$subscription->fresh()->status, $subscription->fresh()->next_billing_at->toDateString()]);
        Carbon::setTestNow();
    }

    public function test_unpaid_subscriptions_go_past_due_after_the_grace_period_and_cancel_ends_them()
    {
        Carbon::setTestNow('2026-09-14');
        config(['shop.subscriptions.grace_days' => 7]);
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(2), 'monthly');
        SubscriptionService::paymentConfirmed($subscription);   // active until 2026-10-14

        Artisan::call('shop:bill-subscriptions', ['--date' => '2026-10-20']);
        $this->assertSame('active', $subscription->fresh()->status, 'within the grace period');
        Artisan::call('shop:bill-subscriptions', ['--date' => '2026-10-21']);
        $this->assertSame('past_due', $subscription->fresh()->status);

        SubscriptionService::paymentConfirmed($subscription->fresh());
        $this->assertSame('active', $subscription->fresh()->status);

        SubscriptionService::cancel($subscription->fresh());
        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertSame(0, Subscription::dueForBilling(Carbon::parse('2030-01-01'))->count());
        Carbon::setTestNow();
    }

    public function test_a_bundle_fee_adds_its_components_at_zero_and_items_can_be_added_and_removed()
    {
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(5), 'monthly');
        $this->assertCount(3, $subscription->items, 'the bundle line and its two components');
        $this->assertSame([17.9, 0.0, 0.0], $subscription->items->pluck('price')->map(fn ($p) => (float) $p)->all());
        $this->assertEqualsWithDelta(17.9 * 1.22, $subscription->total, 0.01);

        $line = SubscriptionService::addItem($subscription, PriceListItem::find(2));
        $this->assertEqualsWithDelta((17.9 + 14.9) * 1.22, $subscription->fresh()->total, 0.01);

        SubscriptionService::removeItem($subscription->items()->whereNull('bundle_code')->orWhere('bundle_code', 0)->where('price', '>', 15)->first());
        $this->assertEqualsWithDelta(14.9 * 1.22, $subscription->fresh()->total, 0.01, 'bundle and its components gone');
        $this->assertCount(1, $subscription->fresh()->items);
    }

    public function test_the_customer_and_admin_pages()
    {
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(2), 'monthly');

        $this->actingAs($this->user);
        $this->get(route('shop.subscriptions'))->assertOk()->assertSee($subscription->shortId);
        $this->get(route('shop.subscription', $subscription))->assertOk()->assertSee('RPD-SUP')->assertSee('Payment due');

        $other = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
        Livewire::actingAs($other)->test('shop::shop-subscription', ['subscription' => $subscription])->assertForbidden();

        $this->actingAs(User::where('email', 'admin@laravel')->firstOrFail());
        $this->get(route('subscriptions.table'))->assertOk()->assertSee($subscription->shortId);
        $this->get(route('subscriptions.view', $subscription))->assertOk()->assertSee('RPD-SUP')->assertSee('Next billing');
    }

    public function test_subscribe_from_the_product_page()
    {
        $this->actingAs($this->user);
        Livewire::test('shop::shop', ['slugs' => 'support-services/priority-support'])
            ->assertSee('Subscribe per month')
            ->assertSee('activation, once')
            ->call('subscribe', 'yearly')
            ->assertRedirect();
        $this->assertSame(1, Subscription::where('user_id', $this->user->id)->where('period', 'yearly')->count());
    }

    public function test_the_pending_record_is_found_whatever_the_period_but_billed_once_per_period()
    {
        $recorder = app(\App\Modules\Shop\Payments\Contracts\PaymentRecorder::class);
        if (! $recorder->available()) {
            $this->markTestSkipped('payments-module not installed');
        }
        $subscription = SubscriptionService::subscribe($this->user, PriceListItem::find(2), 'monthly');
        $first = SubscriptionService::billPeriod($subscription, true);

        // a fresh instance (the customer page) does not know it is the first period
        $fresh = Subscription::find($subscription->id);
        $this->assertSame($first->id, $recorder->findPending($fresh)?->id);
        $this->assertSame($first->id, SubscriptionService::billPeriod($fresh, true)->id, 'billing the same period twice opens one record');
    }
}
