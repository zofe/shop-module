<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Payments\Contracts\PaymentMethod;
use App\Modules\Shop\Payments\ManualPayment;
use App\Modules\Shop\Payments\PaymentMethods;
use App\Modules\Shop\Payments\PaymentStart;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;

class PaymentMethodsTest extends TestCase
{
    use DatabaseMigrations;

    protected function pendingOrder(): Order
    {
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $address = $user->addresses()->create(['address' => 'Rue 1', 'city' => 'Paris', 'zipcode' => '75001', 'country_code' => 'FR']);
        $this->actingAs($user);
        app('cart')->add(PriceListItem::find(1), [], 1); // the licence, 299 € + 20% FR VAT
        $order = OrderService::createOrderFromCart(null, $user->id, null, $address->id);
        \Workflow::get($order, 'order')->apply($order, 'pay_order');
        $order->save();

        return $order->fresh();
    }

    public function test_the_manual_payment_is_the_default_method()
    {
        $methods = app(PaymentMethods::class)->all();
        $this->assertSame(['manual'], $methods->keys()->all(), 'no fake gateways without payments-module');
        $this->assertInstanceOf(ManualPayment::class, $methods['manual']);
    }

    public function test_the_customer_pays_by_bank_transfer()
    {
        config(['shop.manual_payment.instructions' => '{description}: transfer {total} to IBAN IT00, we confirm at {email}.']);
        $order = $this->pendingOrder();

        Livewire::test('shop::orders.orders-checkout-embed', ['order' => $order])
            ->assertSee('Bank transfer')
            ->call('pay', 'manual')
            ->assertSee('Order ' . $order->shortId . ': transfer ' . number_format((float) $order->total, 2) . ' € to IBAN IT00, we confirm at ann@example.com.')
            ->assertSee('awaiting payment confirmation');

        $this->assertSame('payment_verification', $order->fresh()->status);
    }

    public function test_without_methods_the_order_is_simply_registered()
    {
        config(['shop.manual_payment.enabled' => false]);
        $order = $this->pendingOrder();

        Livewire::test('shop::orders.orders-checkout-embed', ['order' => $order])
            ->assertDontSee('Choose a payment method')
            ->assertSee('We will contact you for the payment');
    }

    public function test_a_custom_method_redirects_to_its_gateway()
    {
        app(PaymentMethods::class)->register(new class implements PaymentMethod {
            public function key(): string { return 'acme'; }
            public function label(): string { return 'Acme Pay'; }
            public function description(): string { return 'test'; }
            public function icon(): string { return 'fa-bolt'; }
            public function available(\App\Modules\Shop\Payments\Contracts\Payable $p): bool { return $p->payableAmounts()['total'] > 100; }
            public function start(\App\Modules\Shop\Payments\Contracts\Payable $p, ?object $payment = null): PaymentStart { return PaymentStart::redirect('https://pay.acme.test/' . $p->payableId()); }
        });
        $order = $this->pendingOrder();

        Livewire::test('shop::orders.orders-checkout-embed', ['order' => $order])
            ->assertSee('Acme Pay')
            ->call('pay', 'acme')
            ->assertRedirect('https://pay.acme.test/' . $order->id);

        $this->assertSame('pending_payment', $order->fresh()->status, 'the gateway confirms later (PaymentConfirmed)');
    }

    public function test_only_the_owner_can_check_out()
    {
        $order = $this->pendingOrder();
        $other = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);

        Livewire::actingAs($other)->test('shop::orders.orders-checkout-embed', ['order' => $order])->assertForbidden();
    }
}
