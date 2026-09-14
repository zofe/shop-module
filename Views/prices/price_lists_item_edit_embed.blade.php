<div class="row py-1">
    @if($action == 'edit' || $action == 'create')
        @if($action == 'edit')
            <div class="col-md-3 pt-3">product: {{ $item->name }}</div>
        @elseif($action == 'create')
            <x-rpd::select-list col="col-md-2" model="item.product_id" :options="$products" label="product" placeholder="Product..." />
            <x-rpd::select-list col="col-md-1" model="item.product_variant_id" :options="$this->variantsOf($item->product_id)" label="variant" placeholder="—" />
        @endif
        <div class="col-md-7">
            <div class="row g-2">
                <div class="col-md-3">
                    <x-rpd::checkbox model="item.has_onetime_payment" label="Sold one-time" />
                    <x-rpd::input model="item.price_onetime" label="Price" />
                </div>
                <div class="col-md-3">
                    <x-rpd::checkbox model="item.fee_canbe_monthly" label="Monthly fee" />
                    <x-rpd::input model="item.fee_monthly" label="Fee / month" />
                </div>
                <div class="col-md-3">
                    <x-rpd::checkbox model="item.fee_canbe_yearly" label="Yearly fee" />
                    <x-rpd::input model="item.fee_yearly" label="Fee / year" />
                </div>
                <div class="col-md-3">
                    <x-rpd::checkbox model="item.has_activation_price" label="Activation" />
                    <x-rpd::input model="item.price_activation" label="Activation price" />
                    <x-rpd::input model="item.trial_days" label="Trial days" />
                </div>
            </div>
        </div>
        <div class="col-md-2 flex-center-end">
            <div>
                <x-rpd::icon name="check" click="save" />
                <x-rpd::icon name="rotate-left" x-on:click="$wire.dispatchSelf('toggle')" />
            </div>
        </div>
    @elseif($action == 'show')
        <div class="col-md-4">
            <span class="small text-gray-500">product:</span>
            {{ $item->name }}
            @if($item->product->isBundle())<span class="badge bg-secondary">bundle</span>@endif
        </div>
        <div class="col-md-6 small">
            @if($item->isPurchasable())<span class="me-3"><span class="text-gray-500">one-time</span> {{ number_format($item->price_onetime, 2) }} {{ Cart::currency() }}</span>@endif
            @foreach($item->fees() as $period => $fee)<span class="me-3"><span class="text-gray-500">{{ $period }}</span> {{ number_format($fee, 2) }} {{ Cart::currency() }}</span>@endforeach
            @if($item->activationPrice())<span class="me-3"><span class="text-gray-500">activation</span> {{ number_format($item->activationPrice(), 2) }} {{ Cart::currency() }}</span>@endif
            @if($item->trial_days)<span class="me-3"><span class="text-gray-500">trial</span> {{ $item->trial_days }} days</span>@endif
            @if(! $item->isPurchasable() && ! $item->isSubscribable())<span class="text-muted">not on sale</span>@endif
        </div>
        <div class="col-md-2 flex-center-end">
            <div>
                <x-rpd::icon name="edit" x-on:click="$wire.dispatchSelf('toggle')" />
                <x-rpd::icon name="trash-alt" click="delete()" />
            </div>
        </div>
    @endif
</div>
