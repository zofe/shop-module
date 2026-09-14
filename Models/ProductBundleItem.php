<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

/** A component of a bundle product: a product (or one of its variants) and how many of it. */
class ProductBundleItem extends Model
{
    protected $fillable = ['bundle_product_id', 'product_id', 'product_variant_id', 'qty'];

    protected $casts = ['qty' => 'integer'];

    public function bundle()
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function name(): string
    {
        return $this->product->name . ($this->variant ? ' — ' . $this->variant->name : '');
    }

    public function sku(): ?string
    {
        return $this->variant?->sku ?: $this->product->sku;
    }
}
