<?php

namespace App\Modules\Shop;

use App\Modules\Payments\Events\PaymentConfirmed;
use App\Modules\Shop\Cart\Cart;
use App\Modules\Shop\Listeners\OrderItemAssignmentWorkflowSubscriber;
use App\Modules\Shop\Listeners\OrderWorkflowSubscriber;
use App\Modules\Shop\Listeners\PaymentConfirmedListener;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Payments\GatewayPaymentMethod;
use App\Modules\Shop\Payments\PaymentMethods;
use App\Modules\Shop\Tax\Contracts\TaxResolver;
use App\Modules\Shop\Tax\Contracts\ViesClient;
use App\Modules\Shop\Tax\EuVatResolver;
use App\Modules\Shop\Tax\FlatRateResolver;
use App\Modules\Shop\Tax\TaxManager;
use App\Modules\Shop\Tax\Vies\RestViesClient;
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
        $this->registerPermissions();

        $this->app->bind('cart', Cart::class);

        $this->app->bind(ViesClient::class, RestViesClient::class);
        $this->app->bind(TaxResolver::class, function ($app) {
            $resolver = config('shop.tax_resolver', 'flat');

            return match ($resolver) {
                'flat', null, '' => new FlatRateResolver(),
                'eu_vat'         => $app->make(EuVatResolver::class),
                default          => $app->make($resolver),
            };
        });
        $this->app->singleton(TaxManager::class);

        $this->app->singleton(\App\Modules\Shop\Payments\Contracts\PaymentRecorder::class, function () {
            return class_exists(\App\Modules\Payments\Models\Payment::class)
                ? new \App\Modules\Shop\Payments\Recorders\PaymentsModuleRecorder()
                : new \App\Modules\Shop\Payments\Recorders\NullRecorder();
        });

        $this->app->singleton(PaymentMethods::class, function () {
            $methods = new PaymentMethods();
            if (class_exists(\App\Modules\Payments\PaymentsManager::class)) {
                foreach (config('shop.gateway_methods', []) as $driver => $meta) {
                    $methods->register(new GatewayPaymentMethod($driver, $meta));
                }
            }
            return $methods;
        });

        Relation::morphMap(config('shop.deliverable_types', []), true);
        Relation::morphMap([
            'order'                 => Order::class,
            'order_item_assignment' => OrderItemAssignment::class,
            'subscription'          => \App\Modules\Shop\Models\Subscription::class,
            'subscription_item'     => \App\Modules\Shop\Models\SubscriptionItem::class,
            'license'               => \App\Modules\Shop\Models\License::class,
        ], true);

        $this->app->singleton(\App\Modules\Shop\Provisioning\Provisioners::class);
        $this->app->singleton(\App\Modules\Shop\Documents\Documents::class, function ($app) {
            $renderer = $app->bound(\App\Modules\Shop\Documents\Contracts\DocumentRenderer::class)
                ? $app->make(\App\Modules\Shop\Documents\Contracts\DocumentRenderer::class) : null;

            return new \App\Modules\Shop\Documents\Documents($renderer);
        });
    }

    /** The permissions of the shop join those of rapyd-admin (config auth.*), role by role. */
    protected function registerPermissions(): void
    {
        $permissions = config('shop.permissions', []);
        config(['auth.permissions' => array_values(array_unique(array_merge(config('auth.permissions', []), $permissions)))]);
        foreach (config('shop.role_permissions', []) as $role => $names) {
            $current = config("auth.role_permissions.{$role}");
            if ($current === null && ! in_array($role, config('auth.roles', []), true)) {
                continue;   // a role this application does not have
            }
            config(["auth.role_permissions.{$role}" => array_values(array_unique(array_merge($current ?? [], $names)))]);
        }
    }

    public function boot(): void
    {
        AliasLoader::getInstance()->alias('Cart', CartFacade::class);

        Event::subscribe(OrderWorkflowSubscriber::class);
        Event::subscribe(OrderItemAssignmentWorkflowSubscriber::class);
        Event::subscribe(\App\Modules\Shop\Listeners\SubscriptionWorkflowSubscriber::class);
        Event::subscribe(\App\Modules\Shop\Listeners\ServiceItemWorkflowSubscriber::class);
        if (class_exists(PaymentConfirmed::class)) {
            Event::listen(PaymentConfirmed::class, PaymentConfirmedListener::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([\App\Modules\Shop\Commands\BillSubscriptionsCommand::class]);
        }

        if ($this->isEjected()) {
            return;
        }

        $this->bootAppModule('shop');
    }
}
