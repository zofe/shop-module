<?php

namespace App\Modules\Shop\Livewire\Subscriptions;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Models\SubscriptionItem;
use Livewire\Attributes\On;
use Livewire\Component;

class SubscriptionsView extends Component
{
    use Authorize;
   // use AddRule;

    public $subscription;
    public $current_item;

    public $prd_code;
    public $name;

    public $qty;

    public $newCode;
    public $newName;
    public $newPrice;
    public $newQty;

    public $rmItem;
    public $rmItemDesc;


    protected $payments;

    protected $rules = [
       // 'newModelType'      => 'nullable'
    ];

    #[On('refresh:item')]
    public function handleRefreshItem(): void {}

    public function booted()
    {
        $this->authorize('admin|view subscriptions');
    }

    public function mount(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function changeItem(SubscriptionItem $item)
    {
        $this->authorize('admin|edit subscriptions');
        $this->current_item = $item;
        $this->prd_code = $item->prd_code;
        $this->name = $item->name;
        $this->qty = $item->qty;
        $this->dispatch('show-modal', 'changeItem');
    }

    public function changeItemPrice(SubscriptionItem $item)
    {
        $this->authorize('admin|edit subscriptions');
        $this->current_item = $item;
        $this->coin_price = $item->coin_price;
        $this->prd_code = $item->prd_code;
        $this->name = $item->name;
        $this->qty = $item->qty;
        $this->dispatch('show-modal', 'changeItemPrice');
    }

    public function confirmRemoveItem(SubscriptionItem $item)
    {
        $service_name = '';
        if($item->model_type) {
            $service = app($item->model_type);
            if($service){
                $router = $service::find($item->model_id);
                if($router){
                    $service_name = $router->shortId.' ('.$router->name.')';
                }
            }
        }

        $this->rmItem = $item;
        $this->rmItemDesc = $item->prd_code.' - '.$service_name;

        $this->dispatch('show-modal', 'confirmRemoveItem');
    }


    public function removeItem()
    {
        $this->authorize('admin|edit subscriptions');

        if($this->rmItem) {
            //SubscriptionService::removeItem($this->rmItem);
            $this->subscription->fresh();
            $this->current_item = null;


            $this->dispatch('refresh:item')->self();
            $this->dispatch('livewire-on-messages', message: __('canone aggiornato'));
            $this->dispatch('hide-modals');
        }
    }

    public function addItem()
    {
        $this->newCode = '';
        $this->newName = '';
        $this->newPrice = 0;
        $this->newQty = 1;
        $this->dispatch('show-modal', 'addItem');
    }

    public function addCustomSubscriptionItem()
    {
        $this->authorize('admin|edit subscriptions');
        $this->addRule('newCode','required');
        $this->addRule('newName','required');
        $this->addRule('newPrice','required|regex:/^\d+(\.\d{1,2})?$/');
        $this->addRule('newQty','required|between:1,100');
        $this->validate();

        SubscriptionService::addCustomItem($this->subscription, $this->newCode, $this->newName, $this->newPrice);

        $this->dispatch('refresh:item')->self();
        $this->dispatch('livewire-on-messages', message: __('canone aggiornato'));
        $this->dispatch('hide-modals');
    }

    public function saveSubscriptionItem()
    {
        $this->authorize('admin|edit subscriptions');
        $this->addRule('prd_code','required');
        $this->addRule('name','required');
        $this->addRule('coin_price','required|regex:/^\d+(\.\d{1,2})?$/');
        $this->addRule('qty','required|between:1,100');
        $this->validate();

        $this->current_item->prd_code = $this->prd_code;
        $this->current_item->name = $this->name;
        $this->current_item->updateCoinPrice($this->coin_price, $this->qty);


        $this->dispatch('refresh:item')->self();
        $this->dispatch('livewire-on-messages', message: __('canone aggiornato'));
        $this->dispatch('hide-modals');
    }


    public function render()
    {
        $refTransactions = $this->subscription->reftransactions()->orderBy('created_at','desc')->get();
        $transactions = $this->subscription->transactions()->orderBy('created_at','desc')->get();

        $this->payments = $refTransactions->merge($transactions)->sortByDesc('created_at')->paginate(10);

        $items = $this->subscription->items()->paginate(10);


        return view('shop::subscriptions.subscriptions_view', ['payments'=>$this->payments, 'items'=>$items])->layout('shop::admin');
    }
}
