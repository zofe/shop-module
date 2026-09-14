<?php

namespace App\Modules\Shop\Models;


use App\Modules\Shop\Cart\Contracts\Buyable;
use App\Modules\Shop\Cart\Traits\CanBeBought;
use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model implements Buyable
{
    use CanBeBought;

    protected $table = 'price_list_items';

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getBuyableIdentifier($options = null)
    {
        return $this->id;
    }

    public function getBuyableDescription($options = null)
    {
        return $this->product->name;
    }

    public const PERIODS = ['onetime' => 'price_onetime_customer', 'monthly' => 'price_monthly_customer', 'yearly' => 'price_yearly_customer'];

    /** The price of a billing period (onetime | monthly | yearly), null when the product is not sold that way. */
    public function priceFor(string $period): ?float
    {
        $column = self::PERIODS[$period] ?? null;
        $price = $column ? (float) $this->{$column} : 0.0;

        return $price > 0 ? $price : null;
    }

    /** @return array<string, float> the periods this item is sold with, e.g. ['onetime' => 299.0] */
    public function periods(): array
    {
        $out = [];
        foreach (array_keys(self::PERIODS) as $period) {
            if ($price = $this->priceFor($period)) {
                $out[$period] = $price;
            }
        }

        return $out;
    }

    /** The cart passes ['period' => …]; without it, the first period on sale. */
    public function getBuyablePrice($options = null)
    {
        $period = $options['period'] ?? array_key_first($this->periods()) ?? 'onetime';

        return $this->priceFor($period) ?? 0.0;
    }

    public function getBuyableSku($options = null)
    {
        return $this->product->sku;
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
