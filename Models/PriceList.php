<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A price list: the default one, one per role (customer, partner, reseller…) and
 * lists assigned to single companies (companies.pricelist_id). A product missing
 * from a role's list falls back to the default list.
 */
class PriceList extends Model
{
    protected $table = 'price_lists';

    protected $fillable = ['name', 'role', 'is_active', 'is_default'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_default' => 'boolean'];
    }

    public function items()
    {
        return $this->hasMany(PriceListItem::class)->orderBy('product_id');
    }

    public static function default(): ?self
    {
        return static::where('is_default', 1)->first() ?? static::orderBy('id')->first();
    }

    /** The list of a customer: the company's own, else the one of the company's role, else the default. */
    public static function forCustomer($user = null): ?self
    {
        $company = $user && method_exists($user, 'company') ? $user->company : null;
        if ($company?->pricelist_id && ($list = static::find($company->pricelist_id))) {
            return $list;
        }
        $role = $company?->role ?? $company?->tier ?? null;
        if ($role && ($list = static::where('role', $role)->where('is_active', 1)->first())) {
            return $list;
        }

        return static::default();
    }

    /** The row selling this product (and variant) here, or in the default list when this one lacks it. */
    public function itemFor(int $productId, ?int $variantId = null): ?PriceListItem
    {
        $find = fn (PriceList $list) => $list->items()->where('product_id', $productId)
            ->when($variantId, fn ($q) => $q->where('product_variant_id', $variantId), fn ($q) => $q->whereNull('product_variant_id'))
            ->first();

        if ($item = $find($this)) {
            return $item;
        }
        $default = $this->is_default ? null : static::default();

        return $default ? $find($default) : null;
    }
}
