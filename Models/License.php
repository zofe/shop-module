<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\ShortId;

/** The right to use a service item: activation, expiry (the next billing date for a subscription), owner. */
class License extends Model
{
    use HasUuids, ShortId;

    protected $table = 'licenses';

    protected $guarded = [];

    protected $casts = [
        'activation_date' => 'date',
        'expire_date'     => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** The service item (deliverable_type service_item) */
    public function deliverable()
    {
        return $this->morphTo();
    }

    public function owner()
    {
        return $this->morphTo();
    }

    /** A readable key the customer redeems (XXXX-XXXX-XXXX-XXXX, no ambiguous characters). */
    public static function generateKey(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $key = implode('-', array_map(fn () => substr(str_shuffle($alphabet), 0, 4), range(1, 4)));
        } while (static::where('key', $key)->exists());

        return $key;
    }

    public function isExpired(): bool
    {
        return $this->expire_date !== null && $this->expire_date->isPast();
    }
}
