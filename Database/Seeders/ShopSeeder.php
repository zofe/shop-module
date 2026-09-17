<?php

namespace App\Modules\Shop\Database\Seeders;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItem;
use App\Modules\Shop\Models\PriceList;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductCategory;
use Illuminate\Database\Seeder;


class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = include __DIR__ . '/data.php';

        foreach ($data['categories'] as $row) {
            ProductCategory::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['products'] as $row) {
            $product = Product::firstOrNew(['id' => $row['id']])->fill($row);
            if (! $product->image_path && ! empty($row['image'])) {
                $product->image_path = $this->publishImage($row['image']);
            }
            unset($product->image);
            $product->save();
        }
        foreach ($data['product_variants'] ?? [] as $row) {
            \App\Modules\Shop\Models\ProductVariant::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['product_bundle_items'] ?? [] as $row) {
            \App\Modules\Shop\Models\ProductBundleItem::firstOrCreate($row);
        }
        foreach ($data['price_lists'] as $row) {
            PriceList::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['price_list_items'] as $row) {
            PriceListItem::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        // The demo orders belong to the first user (the admin of rpd:make:setup) and their company, when any.
        $user = config('auth.providers.users.model')::query()->orderBy('created_at')->first();
        $created = [];
        foreach ($data['orders'] as $row) {
            $order = Order::firstOrNew(['id' => $row['id']]);
            if ($order->exists) {
                continue;   // idempotent: an order the demo already moved on is left alone
            }
            $row['user_id']    = $user?->id;
            $row['company_id'] = $user?->company?->id;
            $order->fill($row)->save();
            $created[] = $order;
        }
        foreach ($data['order_items'] as $row) {
            $item = OrderItem::firstOrNew([
                'order_id' => $row['order_id'],
                'prd_code' => $row['prd_code'],
            ])->fill($row);
            $item->save();
            \App\Modules\Shop\Services\OrderService::syncAssignments($item);   // one assignment per unit, as the cart does
        }
        // The orders leave the cart with pay_order ("Make Order"): through the workflow, so the
        // listeners run (the payment record is opened when zofe/payments-module is installed).
        foreach ($created as $order) {
            $order = $order->fresh();
            if ($order->workflow_can('pay_order', 'order')) {
                $order->workflow_apply('pay_order', 'order');
                $order->save();
            }
        }
        foreach ($data['inventory_items'] ?? [] as $row) {
            \App\Modules\Shop\Models\InventoryItem::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }

    }

    /** Copies a demo image (and its _thumb) from this package to the public disk; returns the stored path. */
    protected function publishImage(string $name): ?string
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        foreach (['', '_thumb'] as $suffix) {
            $source = __DIR__ . "/images/{$name}{$suffix}.jpg";
            if (! is_file($source)) {
                return null;
            }
            if (! $disk->exists("products/{$name}{$suffix}.jpg")) {
                $disk->put("products/{$name}{$suffix}.jpg", file_get_contents($source));
            }
        }

        return "products/{$name}.jpg";
    }
}
