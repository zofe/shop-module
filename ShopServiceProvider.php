<?php

namespace App\Modules\Shop;


use App\Modules\Shop\Cart\Cart;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/shop.php', 'shop');

        $this->app->bind('cart', Cart::class);
    }

    public function boot()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Cart', CartFacade::class);
    }
}
