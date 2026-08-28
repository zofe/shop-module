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
        $crumbs->push('Shop', route('shop.list'));

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
    ->crumbs(fn ($crumbs) => $crumbs->parent('shop.list')->push('Shop Cart', route_lang('shop.cart')));
;

Route::get('/shop-orders', \App\Modules\Shop\Livewire\ShopOrders::class)
    ->middleware(['web'])
    ->name('shop.orders')
    ->crumbs(fn ($crumbs) => $crumbs->parent('shop.list')->push('Shop Orders', route_lang('shop.orders')));
;

Route::get('/shop-order/{order}', \App\Modules\Shop\Livewire\ShopOrder::class)
    ->middleware(['web'])
    ->name('shop.order')
    ->crumbs(function ($crumbs, $order) {
        $crumbs->parent('shop.orders')->push('Order Detail', route('shop.order', $order));
    });


//admin
Route::get('/product-categories/tree/{slug?}',\App\Modules\Shop\Livewire\Categories\ProductCategoriesTree::class)
    ->middleware(['web'])
    ->name('product_categories.tree')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Product Categories', route('product_categories.tree')));


Route::get('/products/table', \App\Modules\Shop\Livewire\Products\ProductsTable::class)
    ->middleware(['web'])
    ->name('products.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Products & Services', route('products.table')));

Route::get('/products/view/{product}', \App\Modules\Shop\Livewire\Products\ProductsView::class)
    ->middleware(['web'])
    ->name('products.view')
    ->crumbs(function ($crumbs, $product) {
        $crumbs->parent('products.table')->push('Product Detail', route('products.view', $product));
    });

Route::get('/products/edit/{product?}', \App\Modules\Shop\Livewire\Products\ProductsEdit::class)
    ->middleware(['web'])
    ->name('products.edit')
    ->crumbs(function ($crumbs, $product=null) {
        $crumbs->parent('products.table')->push('Edit Product/Service', route('products.edit'));
    });

Route::get('/pricelists/table', \App\Modules\Shop\Livewire\Prices\PriceListsTable::class)
    ->middleware(['web'])
    ->name('price_lists.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Price Lists', route('price_lists.table')));


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
        $crumbs->parent('price_lists.table')->push($title, route('price_lists.view', $priceList));
    });

Route::get('/orders/table', \App\Modules\Shop\Livewire\Orders\OrdersTable::class)
    ->middleware(['web'])
    ->name('orders.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Orders', route('orders.table')));

Route::get('/orders/view/{order}', \App\Modules\Shop\Livewire\Orders\OrdersView::class)
    ->middleware(['web'])
    ->name('orders.view')
    ->crumbs(fn ($crumbs, $order) => $crumbs->parent('orders.table')->push('Order Detail', route('orders.view', $order)));

Route::get('/subscriptions/table', \App\Modules\Shop\Livewire\Subscriptions\SubscriptionsTable::class)
    ->middleware(['web'])
    ->name('subscriptions.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Subscriptions', route('subscriptions.table')));


Route::get('/inventory-items/table', \App\Modules\Shop\Livewire\Items\InventoryItemsTable::class)
    ->middleware(['web'])
    ->name('inventory_items.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Inventory Items', route('inventory_items.table')));


Route::get('/inventory-items/edit/{item?}', \App\Modules\Shop\Livewire\Items\InventoryItemsEdit::class)
    ->middleware(['web'])
    ->name('inventory_items.edit')
    ->crumbs(function ($crumbs, $item=null) {
        $crumbs->parent('products.table')->push('Edit Inventory Item', route('inventory_items.edit'));
    });

Route::get('/service-items/table', \App\Modules\Shop\Livewire\Items\ServiceItemsTable::class)
    ->middleware(['web'])
    ->name('service_items.table')
    ->crumbs(fn ($crumbs) => $crumbs->parent('admin.home')->push('Service Items', route('service_items.table')));


Route::get('/service-items/edit/{item?}', \App\Modules\Shop\Livewire\Items\ServiceItemsEdit::class)
    ->middleware(['web'])
    ->name('service_items.edit')
    ->crumbs(function ($crumbs, $item=null) {
        $crumbs->parent('products.table')->push('Edit Service Item', route('service_items.edit'));
    });

Route::get('/ajax/available-pricelist-item', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'ajax_available_pricelist_items'])
    ->middleware(['web'])
    ->name('ajax.available_pricelist_items');

//Route::get('/ajax/available-pricelist-item', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'ajax_available_product_items'])
//    ->middleware(['web'])
//    ->name('ajax.available_pricelist_items');
//
//
