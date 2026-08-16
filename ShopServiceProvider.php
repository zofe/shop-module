<?php

namespace App\Modules\Shop;


use App\Modules\Shop\Cart\Cart;

use App\Modules\Shop\Listeners\OrderItemAssignmentWorkflowSubscriber;
use Livewire\Livewire;
use App\Modules\Shop\Listeners\OrderWorkflowSubscriber;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItemAssignment;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/shop.php', 'shop');

        $this->app->bind('cart', Cart::class);

        Relation::morphMap(config("shop.deliverable_types"), true);

        Relation::morphMap([
            'order' => Order::class,
            'order_item_assignment' => OrderItemAssignment::class,
        ], true);

    }

    public function boot()
    {
        Livewire::addNamespace('shop', null, 'App\\Modules\\Shop\\Livewire', __DIR__ . '/Livewire');

        $loader = AliasLoader::getInstance();
        $loader->alias('Cart', CartFacade::class);

        Event::subscribe(OrderWorkflowSubscriber::class);
        Event::subscribe(OrderItemAssignmentWorkflowSubscriber::class);
    }
}
