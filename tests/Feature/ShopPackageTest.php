<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder;

/** The shop registered as a module package (not copied into app/Modules). */
class ShopPackageTest extends TestCase
{
    use DatabaseMigrations;

    public function test_config_views_routes_workflow_and_components_are_registered()
    {
        $this->assertSame('shop::admin', config('shop.layout'), 'config.php merged');
        $this->assertSame(22, config('shop.tax'), 'shop.php merged');
        $this->assertTrue(view()->exists('shop::admin_menu'), 'Views/ registered');
        $this->assertTrue(view()->exists('shop::orders.orders_view'), 'Blade files next to the Livewire classes registered');
        $this->assertTrue(Route::has('orders.table') && Route::has('shop.list'), 'routes loaded');
        $this->assertContains('shop', config('rapyd.modules'), 'module listed');
        $this->assertSame('state_machine', config('workflow.order.type'), 'workflow.php registered');
        $this->assertSame('cart', app('cart')::class === \App\Modules\Shop\Cart\Cart::class ? 'cart' : 'other', 'cart bound');
    }

    public function test_the_products_page_renders_for_the_admin()
    {
        $this->seed(AuthSeeder::class);
        $this->actingAs(User::where('email', 'admin@laravel')->firstOrFail());

        $this->get(route('products.table'))->assertOk()->assertSee('Products');
        Livewire::test('shop::products.products-table')->assertOk();
    }
}
