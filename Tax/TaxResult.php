<?php

namespace App\Modules\Shop\Tax;

/** A rate with its justification: shown to the customer, stored on the order. */
final class TaxResult
{
    public function __construct(
        public readonly float $rate,      // percent, e.g. 22.0
        public readonly string $reason,   // domestic | eu_b2c | eu_reverse_charge | export | flat | unknown_country…
        public readonly string $source,   // flat | eu_vat | the gateway name
        public readonly bool $final = false,
    ) {
    }

    public function amount(float $net): float
    {
        return round($net * $this->rate / 100, 2);
    }

    public function toArray(): array
    {
        return ['rate' => $this->rate, 'reason' => $this->reason, 'source' => $this->source, 'final' => $this->final];
    }
}
