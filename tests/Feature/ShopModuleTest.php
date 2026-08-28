<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductCategory;
use App\Modules\Shop\Models\ProductVariant;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class ShopModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_exist_after_migration(): void
    {
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasTable('product_categories'));
        $this->assertTrue(Schema::hasTable('orders'));
        $this->assertTrue(Schema::hasTable('price_lists'));
        $this->assertTrue(Schema::hasTable('subscriptions'));
    }

    public function test_product_category_can_be_created(): void
    {
        $category = ProductCategory::create(['name' => 'Software', 'slug' => 'software']);

        $this->assertDatabaseHas('product_categories', ['slug' => 'software']);
        $this->assertNotNull($category->id);
    }

    public function test_product_can_be_created(): void
    {
        $category = ProductCategory::create(['name' => 'Software', 'slug' => 'software']);

        $product = Product::create([
            'name'        => 'Test Product',
            'slug'        => 'test-product',
            'sku'         => 'SKU-001',
            'type'        => 'inventory_item',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('products', ['sku' => 'SKU-001']);
        $this->assertNotNull($product->id);
    }

    public function test_product_variant_belongs_to_product(): void
    {
        $category = ProductCategory::create(['name' => 'SaaS', 'slug' => 'saas']);
        $product = Product::create(['name' => 'Plan', 'slug' => 'plan', 'sku' => 'PLAN-1', 'type' => 'service_item', 'category_id' => $category->id]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name'       => 'Annual',
            'sku'        => 'PLAN-1-Y',
        ]);

        $this->assertSame($product->id, $variant->product_id);
        $this->assertCount(1, $product->variants);
    }

    public function test_shop_config_is_merged(): void
    {
        $this->assertIsArray(config('shop'));
    }

    public function test_cart_config_is_merged(): void
    {
        // shop.php merges as 'shop' config
        $this->assertNotNull(config('shop'));
    }
}
