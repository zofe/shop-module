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
            Product::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['price_lists'] as $row) {
            PriceList::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['price_list_items'] as $row) {
            PriceListItem::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        // The demo orders belong to the first user (the admin of rpd:make:setup) and their company, when any.
        $user = config('auth.providers.users.model')::query()->orderBy('created_at')->first();
        foreach ($data['orders'] as $row) {
            $row['user_id']    = $user?->id;
            $row['company_id'] = $user?->company?->id;
            Order::firstOrNew(['id' => $row['id']])->fill($row)->save();
        }
        foreach ($data['order_items'] as $row) {
            OrderItem::firstOrNew([
                'order_id' => $row['order_id'],
                'prd_code' => $row['prd_code'],
            ])->fill($row)->save();
        }

    }
}
