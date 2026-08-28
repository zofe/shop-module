<div class="row g-4">

    @if($price->product->image_path)
        <div class="col-md-5">
            <img src="{{ Storage::url($price->product->image_path) }}"
                 class="img-fluid rounded shadow-sm"
                 style="width:100%; object-fit:cover; max-height:340px;"
                 alt="{{ $price->product->name }}">
        </div>
        <div class="col-md-7">
    @else
        <div class="col-12">
    @endif

            <h3 class="mb-1">{{ $price->product->name }}</h3>
            <span class="badge bg-secondary mb-3">{{ str_replace('_', ' ', $price->product->type) }}</span>

            @if($price->product->description)
                <div class="mb-4 text-muted">{!! $price->product->description !!}</div>
            @endif

            <div class="mb-4">
                @if($price->price_onetime_customer > 0)
                    <div class="fs-4 fw-bold">{{ number_format($price->price_onetime_customer, 2) }} {{ Cart::currency() }}</div>
                    <small class="text-muted">one-time</small>
                @endif
                @if($price->price_yearly_customer > 0)
                    <div class="fs-5 fw-bold">{{ number_format($price->price_yearly_customer, 2) }} {{ Cart::currency() }} <small class="text-muted fw-normal">/year</small></div>
                @endif
                @if($price->price_monthly_customer > 0)
                    <div class="fs-5">{{ number_format($price->price_monthly_customer, 2) }} {{ Cart::currency() }} <small class="text-muted">/month</small></div>
                @endif
            </div>

            <x-rpd::button size="sm" label="Add to cart" click="dispatchSelf('addToCart')" />

        </div>

</div>
