{{-- The product page: image (or a placeholder), the offer, the buy box. The page title above is the product name. --}}
<div class="row g-4 align-items-start">

    <div class="col-md-5">
        @if($price->product->image_path)
            <img src="{{ Storage::url($price->product->image_path) }}"
                 class="img-fluid rounded shadow-sm w-100"
                 style="aspect-ratio: 4 / 3; object-fit: cover;"
                 alt="{{ $price->product->name }}">
        @else
            <div class="rounded d-flex align-items-center justify-content-center text-muted"
                 style="aspect-ratio: 4 / 3; background: var(--bs-tertiary-bg, #f1f3f5);">
                <i class="fas {{ $price->product->type === 'service_item' ? 'fa-concierge-bell' : 'fa-box-open' }} fa-3x opacity-50"></i>
            </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-secondary">{{ str_replace('_', ' ', $price->product->type) }}</span>
            @if($price->product->sku)<span class="text-muted small">{{ __('SKU') }} {{ $price->product->sku }}</span>@endif
            @if($price->product->category)<span class="text-muted small">· {{ $price->product->category->name }}</span>@endif
        </div>

        @if($price->product->description)
            <div class="mb-4">{!! $price->product->description !!}</div>
        @endif

        @php $specs = array_merge($price->variant?->metadata ?? [], $price->metadata ?? []); @endphp
        @if($specs)
            {{-- the attributes of the variant and the parameters of the offer --}}
            <dl class="row small mb-4">
                @foreach($specs as $key => $value)
                    <dt class="col-4 col-md-3 text-muted fw-normal text-capitalize border-0">{{ str_replace('_', ' ', $key) }}</dt>
                    <dd class="col-8 col-md-9 border-0 mb-1">{{ $value }}</dd>
                @endforeach
            </dl>
        @endif

        <div class="border rounded p-3 mb-3" style="background: var(--bs-tertiary-bg, #f8f9fa);">
            @php $fees = $price->fees(); $labels = ['monthly' => 'per month', 'yearly' => 'per year']; @endphp
            @if($price->isPurchasable())
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-2 fw-bold">{{ number_format($price->price_onetime, 2) }} {{ Cart::currency() }}</span>
                    <span class="text-muted">{{ __('one-time') }}</span>
                </div>
            @endif
            @foreach($fees as $period => $fee)
                <div class="d-flex align-items-baseline gap-2 {{ $loop->first && ! $price->isPurchasable() ? '' : 'mt-1' }}">
                    <span class="{{ $loop->first && ! $price->isPurchasable() ? 'fs-2' : 'fs-5' }} fw-bold">{{ number_format($fee, 2) }} {{ Cart::currency() }}</span>
                    <span class="text-muted">{{ $labels[$period] }}</span>
                </div>
            @endforeach
            @if($price->activationPrice())
                <div class="small text-muted mt-1">+ {{ number_format($price->activationPrice(), 2) }} {{ Cart::currency() }} {{ __('activation, once') }}</div>
            @endif
            @if($price->trial_days && $fees)
                <div class="small text-success mt-1"><i class="fas fa-gift me-1"></i>{{ $price->trial_days }} {{ __('days free trial') }}</div>
            @endif
            @if(! $price->isPurchasable() && ! $fees)
                <div class="fs-5 text-muted">{{ __('Contact us for a quote') }}</div>
            @endif
            <div class="small text-muted mt-1">{{ __('Taxes estimated from your billing address.') }}</div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if($price->isPurchasable())
                    <x-rpd::button label="Add to cart" icon="cart-plus" click="dispatchSelf('addToCart')" />
                @endif
                @foreach($fees as $period => $fee)
                    <x-rpd::button :label="'Subscribe ' . $labels[$period]" icon="sync"
                                   :color="$loop->first && ! $price->isPurchasable() ? 'primary' : 'outline-primary'" click="subscribe('{{ $period }}')" />
                @endforeach
                @if(Cart::count() > 0)
                    <a href="{{ route_lang('shop.cart') }}" class="btn btn-outline-secondary"><i class="fas fa-shopping-cart me-1"></i> {{ __('Go to cart') }} ({{ Cart::count() }})</a>
                @endif
            </div>
        </div>

        @if($price->product->isBundle())
            <div class="small text-muted mb-2">Includes:
                @foreach($price->product->bundleItems as $component)
                    <span class="badge bg-light text-dark border">{{ $component->qty > 1 ? $component->qty . ' × ' : '' }}{{ $component->name() }}</span>
                @endforeach
            </div>
        @endif

        @if($price->product->type === 'inventory_item')
            <div class="small text-muted"><i class="fas fa-truck me-1"></i> {{ __('Physical item: shipped to the address you choose at checkout.') }}</div>
        @else
            <div class="small text-muted"><i class="fas fa-bolt me-1"></i> {{ __('Delivered online, no shipping.') }}</div>
        @endif
    </div>

</div>
