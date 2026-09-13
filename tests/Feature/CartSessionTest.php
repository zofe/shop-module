<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Cart\Cart;
use App\Modules\Shop\Cart\CartItem;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Tests\TestCase;

/** The cart survives a session serialized as JSON (Laravel 13 default): items come back as arrays. */
class CartSessionTest extends TestCase
{
    public function test_a_cart_item_is_rebuilt_from_its_array()
    {
        $item = new CartItem(7, 'SKU-1', 'Licence', 299.0, 10.0, 0.5, 2.0, ['plan' => 'pro']);
        $item->qty = 3;
        $item->setTaxRate(22)->setDiscountRate(5)->associate(Product::class);

        $copy = CartItem::fromArray(json_decode(json_encode($item->toArray()), true));

        foreach (['rowId', 'id', 'sku', 'name', 'qty', 'price', 'priceActivation', 'weight', 'shipping', 'taxRate'] as $attr) {
            $this->assertEquals($item->{$attr}, $copy->{$attr}, $attr);
        }
        $this->assertSame('pro', $copy->options->plan);
        $this->assertSame(Product::class, $copy->modelFQCN);
        $this->assertEquals($item->total, $copy->total);
    }

    public function test_the_content_is_hydrated_from_arrays_in_the_session()
    {
        $cart = app('cart');
        $cart->add(7, 'SKU-1', 'Licence', 2, 299.0);
        $rowId = $cart->content()->first()->rowId;

        // What the JSON session driver gives back after a round trip
        $stored = json_decode(json_encode(session()->get('cart.default')), true);
        $this->assertIsArray($stored[$rowId]);
        session()->put('cart.default', $stored);

        $content = app('cart')->content();
        $this->assertInstanceOf(CartItem::class, $content->first());
        $this->assertSame($rowId, $content->keys()->first());
        $this->assertEquals(598.0, app('cart')->subtotal(null, '.', ''));

        app('cart')->update($rowId, 1);
        $this->assertEquals(299.0, app('cart')->subtotal(null, '.', ''));
    }
}
