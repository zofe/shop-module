<?php

use Illuminate\Support\Facades\Route;


//frontend
Route::get('shop/{slugs?}', \App\Modules\Shop\Livewire\Shop::class)
    ->where('slugs', '.*')
    ->middleware(['web'])
    ->name('shop.list')
    ->crumbs(function ($crumbs, $slugs = null) {

        [$price, $category, $categories] = \App\Modules\Shop\Services\ShopService::getContextBySlugs($slugs);

        $path = [];
        $current = $category;
        while ($current) {
            $path[] = $current;
            $current = $current->parent;
        }
        $path = array_reverse($path);

        $crumbs->parent('home');
        $crumbs->push(__('Shop'), route_lang('shop.list'));

        foreach ($path as $cat) {
            $crumbs->push($cat->name, route_lang('shop.list', ['slugs' => $cat->full_path]));
        }

        if ($price) {
            $crumbs->push($price->product->name, route_lang('shop.list', ['slugs' => $price->product->slug]));
        }


    })
;

Route::get('/shop-cart', \App\Modules\Shop\Livewire\ShopCart::class)
    ->middleware(['web'])
    ->name('shop.cart')
    ->crumbs(fn ($crumbs) => $crumbs->parent('shop.list')->push(__('Shop Cart'), route_lang('shop.cart')));
;

Route::get('/shop-orders', \App\Modules\Shop\Livewire\ShopOrders::class)
    ->middleware(['web'])
    ->name('shop.orders')
    ->crumbs(fn ($crumbs) => $crumbs->parent('shop.list')->push(__('Shop Orders'), route_lang('shop.orders')));
;

Route::get('/shop-subscriptions', \App\Modules\Shop\Livewire\ShopSubscriptions::class)
    ->middleware(['web'])
    ->name('shop.subscriptions')
    ->crumbs(fn ($crumbs) => $crumbs->parent('shop.list')->push(__('My subscriptions'), route_lang('shop.subscriptions')));

Route::get('/shop-subscription/{subscription}', \App\Modules\Shop\Livewire\ShopSubscription::class)
    ->middleware(['web'])
    ->name('shop.subscription')
    ->crumbs(fn ($crumbs, $subscription) => $crumbs->parent('shop.subscriptions')->push(__('Subscription') . ' ' . $subscription->shortId, route_lang('shop.subscription', $subscription)));

Route::get('/shop-order/{order}', \App\Modules\Shop\Livewire\ShopOrder::class)
    ->middleware(['web'])
    ->name('shop.order')
    ->crumbs(function ($crumbs, $order) {
        $crumbs->parent('shop.orders')->push(__('Order Detail'), route_lang('shop.order', $order));
    });


//admin
Route::get('/product-categories/tree/{slug?}',\App\Modules\Shop\Livewire\Categories\ProductCategoriesTree::class)
    ->middleware(['web'])
    ->name('product_categories.tree')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Product Categories'), route_lang('product_categories.tree')));


Route::get('/products/table', \App\Modules\Shop\Livewire\Products\ProductsTable::class)
    ->middleware(['web'])
    ->name('products.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Products & Services'), route_lang('products.table')));

Route::get('/products/view/{product}', \App\Modules\Shop\Livewire\Products\ProductsView::class)
    ->middleware(['web'])
    ->name('products.view')
    ->crumbs(function ($crumbs, $product) {
        $crumbs->parent('products.table')->push(__('Product Detail'), route_lang('products.view', $product));
    });

Route::get('/products/edit/{product?}', \App\Modules\Shop\Livewire\Products\ProductsEdit::class)
    ->middleware(['web'])
    ->name('products.edit')
    ->crumbs(function ($crumbs, $product=null) {
        $crumbs->parent('products.table')->push(__('Edit Product/Service'), route_lang('products.edit'));
    });

Route::get('/pricelists/table', \App\Modules\Shop\Livewire\Prices\PriceListsTable::class)
    ->middleware(['web'])
    ->name('price_lists.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Price Lists'), route_lang('price_lists.table')));


Route::get('/pricelists/default', function () {
    $defaultPriceList = \App\Modules\Shop\Models\PriceList::where('is_default', 1)->first();
    if($defaultPriceList) {
        return redirect()->route('price_lists.view', $defaultPriceList);
    } else {
        return redirect()->route('price_lists.table');
    }
})->middleware(['web'])
    ->name('price_lists.default');

Route::get('/pricelists/view/{priceList}', \App\Modules\Shop\Livewire\Prices\PriceListsView::class)
    ->middleware(['web'])
    ->name('price_lists.view')
    ->crumbs(function ($crumbs, $priceList) {
        $title = $priceList->is_default ? 'Default Price list' : 'Price List Detail';
        $crumbs->parent('price_lists.table')->push($title, route_lang('price_lists.view', $priceList));
    });

Route::get('/orders/table', \App\Modules\Shop\Livewire\Orders\OrdersTable::class)
    ->middleware(['web'])
    ->name('orders.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Orders'), route_lang('orders.table')));

// A document of an order / subscription / service item, produced by the bound DocumentRenderer (a documents module).
Route::get('/shop-documents/{type}/{id}/{document}', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'document'])
    ->middleware(['web', 'auth'])
    ->name('shop.document');

Route::get('/orders/view/{order}', \App\Modules\Shop\Livewire\Orders\OrdersView::class)
    ->middleware(['web'])
    ->name('orders.view')
    ->crumbs(fn ($crumbs, $order) => $crumbs->parent('orders.table')->push(__('Order Detail'), route_lang('orders.view', $order)));

Route::get('/orders/view/{order}/impersonate-owner', function (\App\Modules\Shop\Models\Order $order) {
    abort_unless(auth()->user()->canImpersonate(), 403);
    abort_unless($order->user && $order->user->canBeImpersonated(), 403);
    auth()->user()->impersonate($order->user);
    return redirect()->route('shop.order', $order);
})->middleware(['web', 'auth'])->name('orders.impersonate-owner');

Route::get('/orders/pay/{order}', \App\Modules\Shop\Livewire\Orders\OrdersCheckout::class)
    ->middleware(['web', 'auth'])
    ->name('orders.pay')
    ->crumbs(fn ($crumbs, $order) => $crumbs->parent('orders.view', $order)->push(__('Checkout'), route_lang('orders.pay', $order)));

Route::get('/subscriptions/view/{subscription}', \App\Modules\Shop\Livewire\Subscriptions\SubscriptionsView::class)
    ->middleware(['web'])
    ->name('subscriptions.view')
    ->crumbs(fn ($crumbs, $subscription) => $crumbs->parent('subscriptions.table')->push(__('Subscription') . ' ' . $subscription->shortId, route_lang('subscriptions.view', $subscription)));

Route::get('/subscriptions/table', \App\Modules\Shop\Livewire\Subscriptions\SubscriptionsTable::class)
    ->middleware(['web'])
    ->name('subscriptions.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Subscriptions'), route_lang('subscriptions.table')));


Route::get('/inventory-items/table', \App\Modules\Shop\Livewire\Items\InventoryItemsTable::class)
    ->middleware(['web'])
    ->name('inventory_items.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Inventory Items'), route_lang('inventory_items.table')));


Route::get('/inventory-items/edit/{item?}', \App\Modules\Shop\Livewire\Items\InventoryItemsEdit::class)
    ->middleware(['web'])
    ->name('inventory_items.edit')
    ->crumbs(function ($crumbs, $item=null) {
        $crumbs->parent('inventory_items.table')->push($item ? ($item->serial_number ?: __('Unit') . ' ' . $item->shortId) : __('Inventory Item'), route_lang('inventory_items.edit', $item));
    });

Route::get('/service-items/table', \App\Modules\Shop\Livewire\Items\ServiceItemsTable::class)
    ->middleware(['web'])
    ->name('service_items.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push(__('Service Items'), route_lang('service_items.table')));


Route::get('/service-items/edit/{item?}', \App\Modules\Shop\Livewire\Items\ServiceItemsEdit::class)
    ->middleware(['web'])
    ->name('service_items.edit')
    ->crumbs(function ($crumbs, $item=null) {
        $crumbs->parent('service_items.table')->push($item ? __('Service') . ' ' . $item->shortId : __('Service Item'), route_lang('service_items.edit', $item));
    });

Route::get('/ajax/available-pricelist-item', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'ajax_available_pricelist_items'])
    ->middleware(['web'])
    ->name('ajax.available_pricelist_items');

Route::get('/ajax/available-inventory-item', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'ajax_available_inventory_items'])
    ->middleware(['web'])
    ->name('ajax.available_inventory_items');

//Route::get('/ajax/available-pricelist-item', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'ajax_available_product_items'])
//    ->middleware(['web'])
//    ->name('ajax.available_pricelist_items');
//
//
