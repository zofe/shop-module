<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Tax\Contracts\TaxResolver;
use App\Modules\Shop\Tax\Contracts\ViesClient;
use App\Modules\Shop\Tax\EuVatResolver;
use App\Modules\Shop\Tax\FlatRateResolver;
use App\Modules\Shop\Tax\Tax;
use App\Modules\Shop\Tax\TaxContext;
use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Zofe\Rapyd\Modules\Addresses\Models\Address;
use Zofe\Rapyd\Modules\Companies\Models\Company;

class TaxTest extends TestCase
{
    use DatabaseMigrations;

    /** VIES answers: valid for IT/DE numbers ending in 1, invalid otherwise. */
    protected function fakeVies(): void
    {
        $this->app->bind(ViesClient::class, fn () => new class implements ViesClient {
            public function check(string $countryCode, string $vatNumber): ?bool
            {
                return str_ends_with($vatNumber, '1');
            }
        });
        config(['shop.tax_resolver' => 'eu_vat', 'shop.seller_country' => 'IT']);
    }

    protected function resolve(array $ctx): array
    {
        $r = app(TaxResolver::class)->resolve(new TaxContext(...$ctx));

        return [$r->rate, $r->reason];
    }

    public function test_flat_rate_is_the_default()
    {
        config(['shop.tax' => 10]);
        $this->assertInstanceOf(FlatRateResolver::class, app(TaxResolver::class));
        $this->assertSame([10.0, 'flat'], $this->resolve(['countryCode' => 'DE']));
    }

    public function test_the_eu_vat_rules()
    {
        $this->fakeVies();
        $this->assertInstanceOf(EuVatResolver::class, app(TaxResolver::class));

        $this->assertSame([22.0, 'domestic'], $this->resolve(['countryCode' => 'it']), 'same country as the seller');
        $this->assertSame([19.0, 'eu_b2c'], $this->resolve(['countryCode' => 'DE']), 'EU consumer: destination rate');
        $this->assertSame([0.0, 'eu_reverse_charge'], $this->resolve(['countryCode' => 'DE', 'vatNumber' => 'DE123456781', 'isBusiness' => true]), 'EU business, VIES ok');
        $this->assertSame([19.0, 'eu_b2c'], $this->resolve(['countryCode' => 'DE', 'vatNumber' => 'DE123456782', 'isBusiness' => true]), 'EU business, VIES ko: taxed as a consumer');
        $this->assertSame([22.0, 'domestic'], $this->resolve(['countryCode' => 'IT', 'vatNumber' => 'IT123456781', 'isBusiness' => true]), 'Italian business pays Italian VAT');
        $this->assertSame([0.0, 'export'], $this->resolve(['countryCode' => 'US']), 'outside the EU');
        $this->assertSame([0.0, 'export'], $this->resolve(['countryCode' => 'CH']), 'Switzerland is not in the EU');
        $this->assertSame([22.0, 'unknown_country'], $this->resolve([]), 'no address yet: seller rate, flagged');
    }

    public function test_rates_can_be_overridden_from_config()
    {
        $this->fakeVies();
        config(['shop.tax_rates' => ['DE' => 16]]);
        $this->assertSame([16.0, 'eu_b2c'], $this->resolve(['countryCode' => 'DE']));
    }

    public function test_the_rest_vies_client_is_cached()
    {
        $this->app->forgetInstance(ViesClient::class);
        Http::fake(['ec.europa.eu/*' => Http::sequence()->push(['valid' => true])->push('down', 500)]);
        $client = new \App\Modules\Shop\Tax\Vies\RestViesClient();
        $this->assertTrue($client->check('IT', '01234567890'));
        $this->assertTrue($client->check('IT', '01234567890'), 'cached');
        Http::assertSentCount(1);

        $this->assertNull($client->check('IT', '99999999999'), 'no answer is not "invalid"');
        Http::assertSentCount(2);
    }

    public function test_the_context_comes_from_the_company_vat_and_billing_address()
    {
        $this->fakeVies();
        $company = Company::create(['business_name' => 'Acme GmbH', 'vat' => 'DE123456781']);
        $company->addresses()->create(['address' => 'Hauptstr. 1', 'city' => 'Berlin', 'zipcode' => '10115', 'country_code' => 'DE']);
        $user = User::create(['name' => 'Hans', 'email' => 'hans@acme.de', 'password' => 'x', 'company_id' => $company->id]);

        $context = Tax::contextFor($user);
        $this->assertSame(['DE', 'DE123456781', true], [$context->countryCode, $context->vatNumber, $context->isBusiness]);
        $this->assertSame('eu_reverse_charge', Tax::forUser($user)->reason);

        $alone = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $alone->addresses()->create(['address' => '1 Main St', 'city' => 'Austin', 'zipcode' => '73301', 'country_code' => 'US', 'state_code' => 'TX']);
        $this->assertSame('export', Tax::forUser($alone)->reason);
        $this->assertSame('unknown_country', Tax::forUser(null)->reason);
    }

    public function test_the_order_stores_the_estimate()
    {
        $this->fakeVies();
        $user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $user->addresses()->create(['address' => 'Rue 1', 'city' => 'Paris', 'zipcode' => '75001', 'country_code' => 'FR']);
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        app('cart')->add(\App\Modules\Shop\Models\PriceListItem::find(1), 1); // 299 €

        $order = OrderService::createOrderFromCart(null, $user->id);

        $this->assertSame([20.0, 'eu_b2c', 'eu_vat', false], [(float) $order->tax_rate, $order->tax_reason, $order->tax_source, (bool) $order->tax_final]);
        $this->assertEquals(59.80, $order->tax);
        $this->assertEquals(358.80, $order->total);
        $this->assertEquals(20.0, $order->items()->first()->taxRate);
    }

    public function test_the_order_uses_the_chosen_address()
    {
        $this->fakeVies();
        $user = User::create(['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'x']);
        $fr = $user->addresses()->create(['address' => 'Rue 1', 'city' => 'Paris', 'zipcode' => '75001', 'country_code' => 'FR']);
        $us = $user->addresses()->create(['address' => '1 Main St', 'city' => 'Austin', 'zipcode' => '73301', 'country_code' => 'US', 'state_code' => 'TX']);
        $other = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
        $bobs = $other->addresses()->create(['address' => 'Elsewhere', 'city' => 'X', 'zipcode' => '1', 'country_code' => 'DE']);
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);

        app('cart')->add(\App\Modules\Shop\Models\PriceListItem::find(1), 1);
        $order = OrderService::createOrderFromCart(null, $user->id, null, $us->id);
        $this->assertSame(['export', 'US', 'TX'], [$order->tax_reason, $order->shipping_address['country_code'], $order->shipping_address['state_code']]);
        $this->assertEquals(0, $order->tax);

        app('cart')->destroy();
        app('cart')->add(\App\Modules\Shop\Models\PriceListItem::find(1), 1);
        $order = OrderService::createOrderFromCart(null, $user->id, null, $bobs->id);
        $this->assertSame('eu_b2c', $order->tax_reason, "somebody else's address is ignored: back to the first with a country (FR)");
        $this->assertNull($order->shipping_address);
    }

    public function test_the_cart_shows_the_estimate_of_the_customer()
    {
        $this->fakeVies();
        // the address embeds of the cart page check the permissions: a seeded admin
        $this->seed(\Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder::class);
        $user = User::where('email', 'admin@laravel')->firstOrFail();
        $user->addresses()->create(['address' => 'Rue 1', 'city' => 'Paris', 'zipcode' => '75001', 'country_code' => 'FR']);
        app('cart')->add(7, 'SKU', 'Licence', 1, 100.0);

        Livewire::actingAs($user)->test('shop::shop-cart')->assertSee('estimated, 20%')->assertSee('20.00');
        $this->assertEquals(20.0, app('cart')->taxFloat());
    }
}
