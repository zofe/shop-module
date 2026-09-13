<div class="row py-1">


    @if($action == 'edit' || $action == 'create')
        @if($action == 'edit')
            <div class="col-md-3 pt-3">product: {{ $item->product->name }}</div>
        @elseif($action == 'create')
            <x-rpd::select-list col="col-md-3" model="item.product_id" :options="$products" label="product" placeholder="Product..." />
        @endif

        <div class="col-md-6 row">
                <x-rpd::input col="col-md-4" model="item.price_onetime_customer" label="One-off Price" />
                <x-rpd::input col="col-md-4" model="item.price_monthly_customer" label="Monthly Price"  />
                <x-rpd::input col="col-md-4" model="item.price_yearly_customer" label="Yearly Price"  />
        </div>
        <div class="col-md-3 flex-center-end">
            <div>
                <x-rpd::icon name="check" click="save" />
                <x-rpd::icon name="rotate-left" x-on:click="$wire.dispatchSelf('toggle')" />
            </div>
        </div>

    @elseif($action == 'show')

        <div class="col-md-3">
            <span class="small text-gray-500">product:</span>
            {{ $item->product->name }}
        </div>
        <div class="col-md-2 text-end">
            <span class="small text-gray-500">one-off price:</span>
           {{ $item->price_onetime_customer }} {{ Cart::currency() }}
        </div>
        <div class="col-md-2 text-end">
            <span class="small text-gray-500">monthly price:</span>
            {{ $item->price_monthly_customer }} {{ Cart::currency() }}
        </div>
        <div class="col-md-2 text-end">
            <span class="small text-gray-500">yearly price:</span>
            {{ $item->price_yearly_customer }} {{ Cart::currency() }}
        </div>
        <div class="col-md-3 flex-center-end">
            <div>
                <x-rpd::icon name="edit" x-on:click="$wire.dispatchSelf('toggle')" />
                <x-rpd::icon name="trash-alt" click="delete()" />
{{--                <x-rpd::button size="xsm" label="Edit" x-on:click="$wire.dispatchSelf('toggle')" />--}}
{{--                <x-rpd::button size="xsm" label="Delete" color="outline-danger" click="delete()" confirm="delete product price {{ $item->product->name }}?" />--}}
            </div>
        </div>
    @endif

</div>



