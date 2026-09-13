<?php

namespace App\Modules\Shop\Tax\Contracts;

interface ViesClient
{
    /** True when VIES confirms the VAT number; null when VIES could not answer. */
    public function check(string $countryCode, string $vatNumber): ?bool;
}
