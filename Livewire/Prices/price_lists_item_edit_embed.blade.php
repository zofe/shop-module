<div class="row py-1">


    @if($action == 'edit' || $action == 'create')
        @if($action == 'edit')
            <div class="col-md-3 pt-3">product: {{ $item->product->name }}</div>
        @elseif($action == 'create')
            <x-rpd::select-list col="col-md-3" model="item.product_id" :options="$products" label="product" placeholder="Product..." />
        @endif

        <x-rpd::input col="col-md-2" model="item.price_onetime_customer" label="One-off Price" />
        <x-rpd::input col="col-md-2" model="item.price_monthly_customer" label="Monthly Price"  />
        <x-rpd::input col="col-md-2" model="item.price_yearly_customer" label="Yearly Price"  />
        <div class="col-md-3 flex-center-end">
            <div>
                <x-rpd::button size="xsm" color="outline-primary" label="Cancel" x-on:click="$wire.dispatchSelf('toggle')" />
                <x-rpd::button size="xsm" label="Save" click="save" />
            </div>
        </div>

    @elseif($action == 'show')

        <div class="col-md-3">product: {{ $item->product->name }}</div>
        <div class="col-md-2">{{ $item->price_onetime_customer }}</div>
        <div class="col-md-2">{{ $item->price_monthly_customer }}</div>
        <div class="col-md-2">{{ $item->price_yearly_customer }}</div>
        <div class="col-md-3 flex-center-end">
            <div>
                <x-rpd::button size="xsm" label="Edit" x-on:click="$wire.dispatchSelf('toggle')" />
                <x-rpd::button size="xsm" label="Delete" color="outline-danger" click="delete()" confirm="delete product price {{ $item->product->name }}?" />
            </div>
        </div>
    @endif

</div>



