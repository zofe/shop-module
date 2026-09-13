<?php

namespace App\Modules\Shop\Tax;

use App\Modules\Shop\Tax\Contracts\TaxResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Builds the TaxContext of a customer (their company's VAT number and billing
 * address, or the user's address) and asks the configured resolver.
 */
class TaxManager
{
    public function __construct(protected TaxResolver $resolver)
    {
    }

    public function resolver(): TaxResolver
    {
        return $this->resolver;
    }

    public function resolve(TaxContext $context): TaxResult
    {
        return $this->resolver->resolve($context);
    }

    /** The estimate for a user (their company when they have one), null user = anonymous visitor. */
    public function forUser(?Model $user, string $kind = TaxContext::DIGITAL, ?Model $address = null): TaxResult
    {
        return $this->resolve($this->contextFor($user, $kind, $address));
    }

    public function forCompany(?Model $company, string $kind = TaxContext::DIGITAL, ?Model $address = null): TaxResult
    {
        return $this->resolve($this->contextFromCompany($company, $kind, $address));
    }

    /** $address: the one chosen at checkout; otherwise the first with a country of the company or the user. */
    public function contextFor(?Model $user, string $kind = TaxContext::DIGITAL, ?Model $address = null): TaxContext
    {
        if (! $user) {
            return new TaxContext(countryCode: $address?->country_code, stateCode: $address?->state_code, postcode: $address?->zipcode, kind: $kind);
        }

        $company = method_exists($user, 'company') ? $user->company : null;
        if ($company) {
            return $this->contextFromCompany($company, $kind, $address);
        }

        $address ??= method_exists($user, 'addresses') ? $this->billingAddress($user) : null;

        return new TaxContext(
            countryCode: $address?->country_code,
            stateCode: $address?->state_code,
            postcode: $address?->zipcode,
            kind: $kind,
        );
    }

    /** The first address with a country (older rows may have none), else the first one. */
    protected function billingAddress(Model $owner): ?Model
    {
        return $owner->addresses()->whereNotNull('country_code')->first() ?? $owner->addresses()->first();
    }

    protected function contextFromCompany(?Model $company, string $kind, ?Model $address = null): TaxContext
    {
        $address ??= $company && method_exists($company, 'addresses') ? $this->billingAddress($company) : null;

        return new TaxContext(
            countryCode: $address?->country_code,
            stateCode: $address?->state_code,
            postcode: $address?->zipcode,
            vatNumber: $company?->vat ?: null,
            isBusiness: (bool) ($company?->vat),
            kind: $kind,
        );
    }
}
