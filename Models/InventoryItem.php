<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\ShortId;

class InventoryItem extends Model
{
    use HasUuids, ShortId;

    protected $table = 'inventory_items';


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
