<?php

namespace App\Modules\Shop\Cart\Traits;

use App\Modules\Shop\Cart\DefaultCalculator;
use Illuminate\Database\Eloquent\Collection;

trait RecalculatesTotals
{
    public function recalculate(): void
    {
        $calculator = app(config('shop.calculator', DefaultCalculator::class));
        $items = $this->getItems();

        // Calcola tutti i totali in modo coerente
        $this->discount = $calculator->calculateDiscount($items);
        $this->subtotal = $calculator->calculateSubtotal($items);
        $this->shipping = $calculator->calculateShipping($items);
        $this->tax = $calculator->calculateTax($items);
        $this->total = $calculator->calculateTotal($items);

        $this->save();
    }

    public function getTaxRate(): float
    {
        return $this->company ? (float) $this->company->tax_perc : (float) config('cart.tax', 0);
    }

    /**
     * Metodo helper per ottenere gli items come Collection
     */
    abstract public function getItems();
}
