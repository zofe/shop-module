<?php

namespace App\Modules\Shop\Database\Seeders;

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

        $categories = $data['categories'];
        $products = $data['products'];
        $priceLists = $data['price_lists'];
        $priceListItems = $data['price_list_items'];

        foreach ($categories as $category) {
            ProductCategory::firstOrNew($category)->save();
        }
        foreach ($products as $product) {
            Product::firstOrNew($product)->save();
        }
        foreach ($priceLists as $pricelist) {
            PriceList::firstOrNew($pricelist)->save();
        }
        foreach ($priceListItems as $price) {
            PriceListItem::firstOrNew($price)->save();
        }

    }
}
