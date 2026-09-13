<?php

namespace App\Modules\Shop\Tax\Vies;

use App\Modules\Shop\Tax\Contracts\ViesClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** The REST endpoint of VIES (no SOAP extension needed); answers cached for a month. */
class RestViesClient implements ViesClient
{
    public const URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';

    public function check(string $countryCode, string $vatNumber): ?bool
    {
        $key = 'vies:' . strtoupper($countryCode) . $vatNumber;

        return Cache::remember($key, now()->addDays(30), function () use ($countryCode, $vatNumber, $key) {
            try {
                $response = Http::timeout(8)->post(self::URL, ['countryCode' => strtoupper($countryCode), 'vatNumber' => $vatNumber]);
                if (! $response->ok() || ! is_bool($response->json('valid'))) {
                    Log::warning('VIES: no answer', ['vat' => $key, 'status' => $response->status()]);
                    return null;
                }
                return $response->json('valid');
            } catch (\Throwable $e) {
                Log::warning('VIES: unreachable', ['vat' => $key, 'error' => $e->getMessage()]);
                return null;
            }
        });
    }
}
