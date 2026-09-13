<?php

namespace App\Modules\Shop\Tax;

use App\Modules\Shop\Tax\Contracts\TaxResolver;
use App\Modules\Shop\Tax\Contracts\ViesClient;
use Zofe\Rapyd\Support\Countries;

/**
 * The VAT rules of a seller established in the EU:
 * - same country as the seller: the seller's rate (domestic)
 * - EU business with a VAT number confirmed by VIES: 0%, reverse charge
 * - EU consumer (or unconfirmed VAT number): the rate of the customer's country (OSS)
 * - outside the EU: 0%, export / outside the scope of EU VAT
 * - unknown country: the seller's rate, flagged so the checkout can ask for the address
 */
class EuVatResolver implements TaxResolver
{
    public function __construct(protected ViesClient $vies)
    {
    }

    public function resolve(TaxContext $context): TaxResult
    {
        $seller = strtoupper(config('shop.seller_country', 'IT'));
        $homeRate = EuRates::standard($seller) ?? (float) config('shop.tax', 22);
        $country = $context->country();

        if (! $country || ! Countries::has($country)) {
            return new TaxResult($homeRate, 'unknown_country', 'eu_vat');
        }

        if ($country === $seller) {
            return new TaxResult($homeRate, 'domestic', 'eu_vat');
        }

        if (! Countries::isEu($country)) {
            return new TaxResult(0.0, 'export', 'eu_vat');
        }

        if ($context->isBusiness && $context->vatNumber && $this->vatConfirmed($country, $context->vatNumber)) {
            return new TaxResult(0.0, 'eu_reverse_charge', 'eu_vat');
        }

        return new TaxResult(EuRates::standard($country) ?? $homeRate, 'eu_b2c', 'eu_vat');
    }

    protected function vatConfirmed(string $country, string $vatNumber): bool
    {
        $number = preg_replace('/[^A-Z0-9]/', '', strtoupper($vatNumber));
        if (str_starts_with($number, $country)) {
            $number = substr($number, 2);
        } elseif ($country === 'GR' && str_starts_with($number, 'EL')) {
            $number = substr($number, 2);
        }

        return $number !== '' && $this->vies->check($country === 'GR' ? 'EL' : $country, $number) === true;
    }
}
