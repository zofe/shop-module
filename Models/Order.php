<?php

namespace App\Modules\Shop\Models;

use Zofe\Rapyd\Modules\Companies\Models\Company;
use App\Modules\Shop\Cart\Traits\RecalculatesTotals;
use Zofe\Rapyd\Modules\Workflow\Traits\WorkflowTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Modules\Shop\Payments\Contracts\Payable;
use Zofe\Rapyd\Traits\ShortId;

class Order extends Model implements Payable
{
    use HasUuids, ShortId, RecalculatesTotals, WorkflowTrait;

    protected $casts = [
        'shipping_address' => 'array',
        'customer_data'    => 'array',
        'tax_final'        => 'boolean',
        'shipped_at'       => 'datetime',
    ];

    protected $table = 'orders';

    protected function getItems()
    {
        return $this->items;
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('bundle_code');
    }

    public function assignments(): HasManyThrough
    {
        return $this
            ->hasManyThrough(
                OrderItemAssignment::class,
                OrderItem::class,
                'order_id',
                'order_item_id',
                'id',
                'id')
            ->orderBy('deliverable_type');
    }

    /** Physical goods to assign and ship (inventory items), as opposed to services. */
    public function hasPhysicalItems(): bool
    {
        return $this->assignments()->where('order_items_assignments.deliverable_type', 'inventory_item')->exists();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ---- Payable -----------------------------------------------------------

    public function payableType(): string
    {
        return 'order';
    }

    public function payableId(): string
    {
        return (string) $this->id;
    }

    public function payableDescription(): string
    {
        return 'Order ' . $this->shortId;
    }

    public function payableAmounts(): array
    {
        return [
            'discount' => (float) $this->discount, 'subtotal' => (float) $this->subtotal, 'shipping' => (float) $this->shipping,
            'tax' => (float) $this->tax, 'total' => (float) $this->total,
        ];
    }

    public function payableItems(): array
    {
        $taxRate = (float) ($this->tax_rate ?? config('shop.tax', 22));

        return $this->items->map(fn ($item) => [
            'name'             => $item->name,
            'prd_code'         => $item->prd_code,
            'order_item_id'    => $item->id,
            'price_list_item_id' => $item->price_list_item_id,
            'deliverable_type' => $item->deliverable_type,
            'qty'              => (float) $item->qty,
            'price'            => (float) $item->price,
            'subtotal'         => (float) $item->subtotal,
            'shipping'         => (float) ($item->shipping ?? 0),
            'discountRate'     => (float) ($item->discountRate ?? 0),
            'discount'         => 0.0,
            'taxRate'          => $taxRate,
            'tax'              => round((float) $item->subtotal * $taxRate / 100, 2),
            'total'            => round((float) $item->subtotal * (1 + $taxRate / 100), 2),
        ])->all();
    }

    public function payableUser()
    {
        return $this->user;
    }

    public function payableCompany()
    {
        return $this->company;
    }

    public function payableCustomerEmail(): ?string
    {
        return $this->user?->email;
    }

    public function payableLinks(): array
    {
        return ['order_id' => $this->id];
    }
}
