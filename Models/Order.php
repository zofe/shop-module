<?php

namespace App\Modules\Shop\Models;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Shop\Cart\Traits\RecalculatesTotals;
use App\Modules\Workflow\Traits\WorkflowTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Zofe\Rapyd\Traits\ShortId;

class Order extends Model
{
    use HasUuids, ShortId, RecalculatesTotals, WorkflowTrait;

    protected $table = 'orders';

    protected function getItems()
    {
        return $this->items;
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('bundle_code');
    }

    public function assignments(): HasManyThrough
    {
        return $this
            ->hasManyThrough(
                OrderItemAssignment::class,
                OrderItem::class,
                'order_id',
                'order_item_id',
                'id',
                'id')
            ->orderBy('deliverable_type');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
