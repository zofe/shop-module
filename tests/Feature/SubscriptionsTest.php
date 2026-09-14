<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Order;
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

class SubscriptionsTest extends TestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $this->user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $this->user->addresses()->create(['address' => 'Via Roma 1', 'city' => 'Bari', 'zipcode' => '70100', 'country_code' => 'IT']);
        config(['shop.tax_resolver' => 'flat', 'shop.tax' => 22]);
    }

    /** The support plan (item 2) added for a period, the order paid. */
    protected function paidOrder(string $period = 'monthly'): Order
    {
        app('cart')->destroy();
        app('cart')->add(PriceListItem::find(2), ['period' => $period], 1);
        $order = OrderService::createOrderFromCart(null, $this->user->id);
        $workflow = \Workflow::get($order, 'order');
        foreach (['pay_order', 'check_payment', 'payment_done'] as $t) {
            $workflow->apply($order, $t);
            $order->save();
        }

        return $order->fresh();
    }

    public function test_a_price_list_item_knows_its_periods_and_the_cart_prices_them()
    {
        $support = PriceListItem::find(2);
        $this->assertSame(['monthly' => 14.9, 'yearly' => 149.0], $support->periods());
        $this->assertSame(['onetime' => 299.0], PriceListItem::find(1)->periods());

        app('cart')->add($support, ['period' => 'yearly'], 1);
        $item = app('cart')->content()->first();
        $this->assertSame([149.0, 'yearly'], [(float) $item->price, $item->options->period]);
    }

    public function test_paying_an_order_with_a_recurring_line_creates_the_subscription()
    {
        Carbon::setTestNow('2026-09-14');
        $order = $this->paidOrder('monthly');

        $this->assertSame('payment_done', $order->status);
        $this->assertNotNull($order->subscription_id);
        $subscription = $order->subscription;
        $this->assertSame(['monthly', 'active', 'shop', '2026-09-14', '2026-10-14'], [
            $subscription->period, $subscription->status, $subscription->managed_by,
            $subscription->start_date->toDateString(), $subscription->next_billing_at->toDateString(),
        ]);
        $this->assertSame($order->id, $subscription->order_id);
        $this->assertCount(1, $subscription->items);
        $item = $subscription->items->first();
        $this->assertSame(['RPD-SUP-YEAR', 'monthly', 14.9, 1], [$item->prd_code, $item->period, (float) $item->price, (int) $item->qty]);
        $this->assertSame($order->items->first()->id, $item->order_item_id);
        $this->assertEqualsWithDelta(14.9 * 1.22, $subscription->total, 0.01);

        // paying again (a second webhook) does not create another one
        SubscriptionService::onOrderPaid($order->fresh());
        $this->assertSame(1, Subscription::count());
        Carbon::setTestNow();
    }

    public function test_one_time_lines_create_no_subscription()
    {
        $address = $this->user->addresses()->first();
        app('cart')->add(PriceListItem::find(1), ['period' => 'onetime'], 1);
        $order = OrderService::createOrderFromCart(null, $this->user->id, null, $address->id);
        $this->assertNull(SubscriptionService::onOrderPaid($order));
        $this->assertSame(0, Subscription::count());
    }

    public function test_the_renewal_command_creates_an_order_when_the_billing_date_comes_and_its_payment_extends_the_subscription()
    {
        Carbon::setTestNow('2026-09-14');
        $subscription = $this->paidOrder('yearly')->subscription;
        $this->assertSame('2027-09-14', $subscription->next_billing_at->toDateString());

        Artisan::call('shop:renew-subscriptions', ['--date' => '2027-09-13']);
        $this->assertStringContainsString('0 renewal', Artisan::output(), 'not yet');

        Artisan::call('shop:renew-subscriptions', ['--date' => '2027-09-14']);
        $this->assertStringContainsString('1 renewal', Artisan::output());
        $renewal = $subscription->renewals()->first();
        $this->assertSame(['renewal', 'pending_payment', 149.0], [$renewal->kind, $renewal->status, (float) $renewal->subtotal]);
        $this->assertSame('yearly', $renewal->items->first()->period);

        Artisan::call('shop:renew-subscriptions', ['--date' => '2027-09-14']);
        $this->assertStringContainsString('0 renewal', Artisan::output(), 'a pending renewal is not duplicated');

        $workflow = \Workflow::get($renewal, 'order');
        foreach (['check_payment', 'payment_done'] as $t) {
            $workflow->apply($renewal, $t);
            $renewal->save();
        }
        $this->assertSame('2028-09-14', $subscription->fresh()->next_billing_at->toDateString());
        $this->assertSame(1, Subscription::count(), 'a renewal never creates a new subscription');
        Carbon::setTestNow();
    }

    public function test_subscriptions_managed_by_a_gateway_are_not_renewed_by_the_shop()
    {
        Carbon::setTestNow('2026-09-14');
        config(['shop.subscriptions.managed_by' => 'stripe']);
        $subscription = $this->paidOrder('monthly')->subscription;
        $this->assertSame('stripe', $subscription->managed_by);

        Artisan::call('shop:renew-subscriptions', ['--date' => '2026-12-01']);
        $this->assertStringContainsString('0 renewal', Artisan::output());
        Carbon::setTestNow();
    }

    public function test_cancel_and_the_admin_pages()
    {
        $subscription = $this->paidOrder('monthly')->subscription;
        $this->seed(AuthSeeder::class);
        $this->actingAs(User::where('email', 'admin@laravel')->firstOrFail());

        $this->get(route('subscriptions.table'))->assertOk()->assertSee($subscription->shortId)->assertSee('monthly');
        $this->get(route('subscriptions.view', $subscription))->assertOk()->assertSee('RPD-SUP-YEAR')->assertSee('Next billing');

        Livewire::test('shop::subscriptions.subscriptions-view', ['subscription' => $subscription])
            ->call('renewNow')->assertRedirect();
        $this->assertSame(1, $subscription->renewals()->count());

        SubscriptionService::cancel($subscription);
        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertSame(0, Subscription::dueForRenewal(now()->addYear())->count());
    }
}
