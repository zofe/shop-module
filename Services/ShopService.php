<?php

namespace App\Modules\Shop\Services;



use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Models\ProductCategory;

class ShopService
{
    public static function getContextBySlugs($slugs)
    {

        if (!$slugs) {
            $categories = ProductCategory::whereNull('parent_id')->orderBy('order')->get();
            return [null, null, $categories];
        }

        $slugParts = explode('/', $slugs);
        $slug = end($slugParts);

        $price = PriceListItem::whereHas('product', fn ($q) => $q->where('slug', $slug))->first();

        if ($price) {
            $category = $price->product->category;
        } else {
            $category = ProductCategory::where('slug', $slug)->first();
            if (!$category || $category->full_path !== $slugs) {
                return [null, null, []];
            }
        }
        $categories = $category->children ?? [];

        return [$price, $category, $categories];
    }
}

