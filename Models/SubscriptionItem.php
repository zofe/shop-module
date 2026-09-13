<?php

namespace App\Modules\Shop\Models;


use App\Modules\Shop\Cart\DefaultCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class SubscriptionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'subscription_items';

    protected $casts = [
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($item){
            if($item->model_type && $item->model_id) {
                $expiring = new SubscriptionItemExpiring();
                $expiring->deliverable_type = $item->deliverable_type;
                $expiring->deliverable_id = $item->deliverable_id;

                $expiring->subscription_id = $item->subscription_id;
                $expiring->expire_date = now()->endOfMonth()->addDay()->setTime(8,0)->toDateTimeString();
                $expiring->save();
            }
        });

        static::deleted(function ($item){
            $item->subscription->recalculate();
        });

        static::creating(function ($item){
            $item->subtotal = $item->price * $item->qty;
            $item->taxRate = ($item->subscription->company) ? $item->subscription->company->tax_perc : config('shop.tax', 22);
            $item->total = $item->getCalculated('total');
        });

        static::created(function($item){
            $item->subscription->recalculate();
            if($item->model_type && $item->model_id) {
                SubscriptionItemExpiring::whereDeliverableType($item->deliverable_type)
                    ->whereDeliverableId($item->deliverable_id)->delete();
            }
        });

        static::updated(function($item){
            $item->subscription->recalculate();
        });
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }

    public function updatePriceQty($newPrice, $qty, $shipping = null)
    {
        $this->price = $newPrice;
        $this->qty = $qty;

        if ($shipping !== null) {
            $this->shipping = $shipping;
        }

        // Usa il Calculator per i campi persistenti
        $this->subtotal = DefaultCalculator::getAttribute('subtotal', $this);

        $this->save();
        $this->subscription->recalculate();
    }

    public function getCalculated(string $attribute)
    {
        $calculator = config('shop.calculator', DefaultCalculator::class);
        return $calculator::getAttribute($attribute, $this);
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }


//    public function model()
//    {
//        return $this->morphTo();
//    }
}
