<?php

namespace App\Modules\Shop;

use App\Modules\Payments\Events\PaymentConfirmed;
use App\Modules\Shop\Cart\Cart;
use App\Modules\Shop\Listeners\OrderItemAssignmentWorkflowSubscriber;
use App\Modules\Shop\Listeners\OrderWorkflowSubscriber;
use App\Modules\Shop\Listeners\PaymentConfirmedListener;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItemAssignment;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;
use Zofe\Rapyd\Modules\RapydModuleServiceProvider;

/**
 * The shop as a module package: bootAppModule() loads migrations, views (the
 * Blade files next to the Livewire classes included), routes, workflow.php
 * and the "shop::" components; config.php is merged as config('shop').
 * Symlinked or copied into app/Modules/Shop it keeps working: isEjected()
 * steps aside and the app's ModuleServiceProvider takes over the loading.
 */
class ShopServiceProvider extends RapydModuleServiceProvider
{
    protected string $moduleName = 'Shop';

    protected ?string $modulePath = __DIR__;

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/shop.php', 'shop');

        $this->app->bind('cart', Cart::class);

        Relation::morphMap(config('shop.deliverable_types', []), true);
        Relation::morphMap([
            'order'                 => Order::class,
            'order_item_assignment' => OrderItemAssignment::class,
        ], true);
    }

    public function boot(): void
    {
        AliasLoader::getInstance()->alias('Cart', CartFacade::class);

        Event::subscribe(OrderWorkflowSubscriber::class);
        Event::subscribe(OrderItemAssignmentWorkflowSubscriber::class);
        if (class_exists(PaymentConfirmed::class)) {
            Event::listen(PaymentConfirmed::class, PaymentConfirmedListener::class);
        }

        if ($this->isEjected()) {
            return;
        }

        $this->bootAppModule('shop');
    }
}
