<?php

namespace App\Modules\Shop\Models;


use App\Modules\Shop\Cart\Contracts\BuyableItem;
use App\Modules\Shop\Cart\DefaultCalculator;
use App\Modules\Workflow\Traits\WorkflowTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAssignment extends Model implements BuyableItem
{
    use WorkflowTrait;

    protected $table = 'order_items_assignments';

    protected $fillable = [
        'order_item_id',
        'serial_number',
        'metadata',
        'deliverable_id',
        'deliverable_type',
        'license_id',
        'invoice_id',
        'status',
        'subtotal',
        'tax',
        'total'
    ];

    protected $casts = [
        'metadata' => 'array',
        'subtotal' => 'double',
        'tax' => 'double',
        'total' => 'double',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'id');
    }

    public function deliverable()
    {
        return $this->morphTo();
    }


    public function getDiscountRate(): float
    {
        return $this->discount_rate ?? 0;
    }

    public function updatePriceQty($newPrice, $qty, $shipping = null)
    {
        $this->price = $newPrice;
        $this->qty = $qty;

        if ($shipping !== null) {
            $this->shipping = $shipping;
        }

        // Usa il Calculator per i campi persistenti
        $this->subtotal = DefaultCalculator::getAttribute('subtotal', $this);

        $this->save();
        $this->order->recalculate();
    }

    /**
     * Metodo helper per ottenere valori calcolati
     * (opzionale, da usare al posto di accessor)
     */
    public function getCalculated(string $attribute)
    {
        $calculator = config('shop.calculator', DefaultCalculator::class);
        return $calculator::getAttribute($attribute, $this);
    }
}
