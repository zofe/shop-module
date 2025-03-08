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

    public function getBuyablePrice($options = null)
    {
        return $this->price_onetime_customer;
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
