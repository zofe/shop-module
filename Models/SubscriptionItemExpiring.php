<?php

namespace App\Modules\Shop\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Modules\Shop\Models\SubscriptionItemExpiring
 *
 * @property int $id
 * @property string|null $subscription_id
 * @property string $expire_date
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring newQuery()
 * @method static \Illuminate\Database\Query\Builder|SubscriptionItemExpiring onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereExpireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionItemExpiring whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|SubscriptionItemExpiring withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SubscriptionItemExpiring withoutTrashed()
 * @mixin \Eloquent
 */
class SubscriptionItemExpiring extends Model
{
    use SoftDeletes;

    protected $table = 'subscription_items_expiring';

    protected $fillable = [
        '*'
    ];

//todo morph relation
//    public function box()
//    {
//        return $this->belongsTo(Box::class, 'box_id', 'id');
//    }


}
