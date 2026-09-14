<?php

namespace App\Modules\Shop\Models;

use App\Modules\Shop\Cart\Contracts\Buyable;
use App\Modules\Shop\Cart\Traits\CanBeBought;
use Illuminate\Database\Eloquent\Model;

/**
 * How a product (or one of its variants) is sold in a price list:
 * - has_onetime_payment / price_onetime: bought once, through the cart (an order)
 * - fee_canbe_monthly / fee_monthly, fee_canbe_yearly / fee_yearly: a recurring fee (a subscription)
 * - has_activation_price / price_activation: charged once with the first fee
 * - trial_days: the subscription starts free, the first fee is due at the end of the trial
 */
class PriceListItem extends Model implements Buyable
{
    use CanBeBought;

    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id', 'product_id', 'product_variant_id', 'metadata',
        'has_onetime_payment', 'price_onetime', 'has_activation_price', 'price_activation',
        'fee_canbe_monthly', 'fee_monthly', 'fee_canbe_yearly', 'fee_yearly', 'trial_days',
    ];

    protected $casts = [
        'metadata' => 'array',
        'has_onetime_payment' => 'boolean', 'has_activation_price' => 'boolean',
        'fee_canbe_monthly' => 'boolean', 'fee_canbe_yearly' => 'boolean',
        'price_onetime' => 'float', 'price_activation' => 'float', 'fee_monthly' => 'float', 'fee_yearly' => 'float',
        'trial_days' => 'integer',
    ];

    public const FEE_PERIODS = ['monthly', 'yearly'];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** "Product name" or "Product name — Variant name". */
    public function getNameAttribute(): string
    {
        return $this->product->name . ($this->variant ? ' — ' . $this->variant->name : '');
    }

    public function getSkuAttribute(): ?string
    {
        return $this->variant?->sku ?: $this->product->sku;
    }

    // ---- what is on sale ---------------------------------------------------

    public function isPurchasable(): bool
    {
        return $this->has_onetime_payment && $this->price_onetime > 0;
    }

    /** @return array<string, float> the fee periods on sale, e.g. ['monthly' => 14.9, 'yearly' => 149.0] */
    public function fees(): array
    {
        $fees = [];
        foreach (self::FEE_PERIODS as $period) {
            if ($this->{"fee_canbe_{$period}"} && $this->{"fee_{$period}"} > 0) {
                $fees[$period] = (float) $this->{"fee_{$period}"};
            }
        }

        return $fees;
    }

    public function isSubscribable(): bool
    {
        return $this->fees() !== [];
    }

    public function fee(string $period): ?float
    {
        return $this->fees()[$period] ?? null;
    }

    public function activationPrice(): float
    {
        return $this->has_activation_price ? (float) $this->price_activation : 0.0;
    }

    // ---- Buyable (the cart: one-time purchases only) ----------------------

    public function getBuyableIdentifier($options = null)
    {
        return $this->id;
    }

    public function getBuyableDescription($options = null)
    {
        return $this->name;
    }

    public function getBuyablePrice($options = null)
    {
        return $this->isPurchasable() ? (float) $this->price_onetime : 0.0;
    }

    public function getBuyableSku($options = null)
    {
        return $this->sku;
    }

    public function getBuyablePriceActivation($options = null)
    {
        return 0;
    }

    public function getBuyableWeight($options = null)
    {
        return 0;
    }

    public function getBuyableShipping($options = null)
    {
        return 0;
    }
}
