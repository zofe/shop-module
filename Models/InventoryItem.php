<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\ShortId;

class InventoryItem extends Model
{
    use HasUuids, ShortId;

    protected $table = 'inventory_items';

    protected $fillable = [
        'product_id',
        'status',
        'serial_number',
        'qty',
        'owner_id',
        'owner_type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
