<?php

namespace App\Modules\Shop\Tax;

/**
 * Standard VAT rates of the EU member states (percent), September 2026.
 * Override or extend with config('shop.tax_rates') when a rate changes.
 */
final class EuRates
{
    public const STANDARD = [
        'AT' => 20.0, 'BE' => 21.0, 'BG' => 20.0, 'HR' => 25.0, 'CY' => 19.0, 'CZ' => 21.0, 'DK' => 25.0,
        'EE' => 24.0, 'FI' => 25.5, 'FR' => 20.0, 'DE' => 19.0, 'GR' => 24.0, 'HU' => 27.0, 'IE' => 23.0,
        'IT' => 22.0, 'LV' => 21.0, 'LT' => 21.0, 'LU' => 17.0, 'MT' => 18.0, 'NL' => 21.0, 'PL' => 23.0,
        'PT' => 23.0, 'RO' => 21.0, 'SK' => 23.0, 'SI' => 22.0, 'ES' => 21.0, 'SE' => 25.0,
    ];

    public static function standard(string $countryCode): ?float
    {
        $rates = array_merge(self::STANDARD, config('shop.tax_rates', []));

        return $rates[strtoupper($countryCode)] ?? null;
    }
}
