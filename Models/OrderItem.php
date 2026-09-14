<?php

namespace App\Modules\Shop\Models;


use App\Modules\Shop\Cart\Contracts\BuyableItem;
use App\Modules\Shop\Cart\DefaultCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model implements BuyableItem
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'price_list_item_id', 'name', 'qty', 'price', 'subtotal', 'discountRate', 'taxRate', 'shipping',
        'bundle_code', 'prd_code', 'deliverable_type', 'product_variant_id'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OrderItemAssignment::class);
    }

    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(PriceListItem::class, 'price_list_item_id', 'id');
    }


    public function getDiscountRate(): float
    {
        return $this->discount_rate ?? 0;
    }

    public function updatePriceQty($price, $qty, $shipping = null, $code = null, $name = null)
    {
        $this->fill([
            'price'     => $price,
            'qty'       => $qty,
            'shipping'  => $shipping ?? $this->shipping,
            'prd_code'  => $code ?? $this->prd_code,
            'name'      => $name ?? $this->name,
        ]);

        $this->subtotal = DefaultCalculator::getAttribute('subtotal', $this);

        $this->save();
        $this->order->recalculate();
    }

    public function getCalculated(string $attribute)
    {
        $calculator = config('shop.calculator', DefaultCalculator::class);
        return $calculator::getAttribute($attribute, $this);
    }
}
