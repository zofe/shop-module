
<div>
    <h3>{{ $price->product->name }}</h3>

    <p>{!! $price->product->description !!} </p>


    <div>{{ $price->price_onetime_customer }} {{ Cart::currency() }}</div>

    <div class="mt-2">
        <x-rpd::button size="sm" label="Add to cart" click="dispatchSelf('addToCart')" />
    </div>
</div>


