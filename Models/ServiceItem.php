<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\ShortId;

class ServiceItem extends Model
{
    use HasUuids, ShortId;

    protected $table = 'service_items';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
