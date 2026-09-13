<?php

namespace App\Modules\Shop\Models;


use App\Modules\Shop\Cart\Traits\RecalculatesTotals;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\SSearch;
use Zofe\Rapyd\Traits\ShortId;


class Subscription extends Model
{
    use HasUuids, ShortId, RecalculatesTotals, SSearch;

    public static array $searchableColumns = ['id', 'description', 'status'];

    protected $table = 'subscriptions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'start_date' => 'date',
        'try_count'  => 'array',
    ];

    protected function getItems()
    {
        return $this->items;
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function company()
    {
        return $this->belongsTo(\Zofe\Rapyd\Modules\Companies\Models\Company::class);
    }

    public function items()
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id');
    }

    public function itemsBefore(Carbon $payment_date)
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id')
            ->where('created_at','<', $payment_date->format('Y-m-d'));
    }

    public function itemsActive()
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id')
            ->where(function ($q) {
                $q->where('preact_status','=','active')
                    ->OrWhereNull('preact_status')
                ;
            });


    }

    public function removeService($deliverable_type, $deliverable_id, $force = false)
    {
        $toRemove = SubscriptionItem::where('subscription_id','=',$this->id)
            ->where('deliverable_type','=',$deliverable_type)
            ->where('deliverable_id','=',$deliverable_id)
            ->get()->first();
        if($toRemove) {
            if($force) {
                $toRemove->forceDelete();
            } else {
                $toRemove->delete();
            }

        }
        $this->recalculate();

    }


}
