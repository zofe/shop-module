<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name', 'slug', 'sku', 'sku_type', 'type', 'provisioner', 'activation',
        'category_id', 'description', 'image_path',
    ];


    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function getIsServiceAttribute()
    {
        return $this->type === 'service_item';
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isBundle(): bool
    {
        return $this->type === 'bundle';
    }

    /** The components of a bundle product, sold together at the bundle's own price. */
    public function bundleItems()
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_product_id');
    }

    /** The delivery type of a product: its own, or for a bundle the one of its components (physical wins). */
    /** How a sold unit of this service is activated: the product's choice, else the shop's (config shop.provisioning.activation). */
    public function activationPolicy(): string
    {
        return $this->activation ?: config('shop.provisioning.activation', 'automatic');
    }

    public function deliverableType(): string
    {
        if (! $this->isBundle()) {
            return $this->type;
        }

        return $this->bundleItems->contains(fn ($c) => $c->product->type === 'inventory_item') ? 'inventory_item' : 'service_item';
    }

    public function getFullPathAttribute()
    {
        return $this->category->full_path . '/' . $this->slug;
    }

    public function getThumbPathAttribute(): ?string
    {
        if (!$this->image_path) return null;
        return \Illuminate\Support\Str::beforeLast($this->image_path, '.jpg') . '_thumb.jpg';
    }
}
