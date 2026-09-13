<?php

namespace App\Modules\Shop\Tax;

use Illuminate\Support\Facades\Facade;

/**
 * @method static TaxResult resolve(TaxContext $context)
 * @method static TaxResult forUser(?\Illuminate\Database\Eloquent\Model $user, string $kind = 'digital')
 * @method static TaxResult forCompany(?\Illuminate\Database\Eloquent\Model $company, string $kind = 'digital')
 * @method static TaxContext contextFor(?\Illuminate\Database\Eloquent\Model $user, string $kind = 'digital')
 * @method static Contracts\TaxResolver resolver()
 */
class Tax extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TaxManager::class;
    }
}
