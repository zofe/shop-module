<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A sellable variant of a product: a size, a colour, a plan tier, a licence size. Own SKU, stock and price list rows. */
class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'name', 'sku', 'stock', 'metadata'];   // the price is in the price list rows

    protected $casts = ['metadata' => 'array', 'stock' => 'integer'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceListItems()
    {
        return $this->hasMany(PriceListItem::class, 'product_variant_id');
    }
}
