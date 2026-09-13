<?php

namespace App\Modules\Shop\Tax;

/** What a tax rule needs to know about a sale. Built by TaxManager from the customer. */
final class TaxContext
{
    public const DIGITAL = 'digital';
    public const PHYSICAL = 'physical';

    public function __construct(
        public readonly ?string $countryCode = null,   // ISO 3166-1 alpha-2 of the billing address
        public readonly ?string $stateCode = null,     // ISO 3166-2 subdivision (US, CA…)
        public readonly ?string $postcode = null,
        public readonly ?string $vatNumber = null,     // with the country prefix, e.g. IT01234567890
        public readonly bool $isBusiness = false,
        public readonly string $kind = self::DIGITAL,  // digital | physical
    ) {
    }

    public function country(): ?string
    {
        return $this->countryCode ? strtoupper($this->countryCode) : null;
    }
}
