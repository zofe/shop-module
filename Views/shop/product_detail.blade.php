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
            @if($price->product->sku)<span class="text-muted small">SKU {{ $price->product->sku }}</span>@endif
            @if($price->product->category)<span class="text-muted small">· {{ $price->product->category->name }}</span>@endif
        </div>

        @if($price->product->description)
            <div class="mb-4">{!! $price->product->description !!}</div>
        @endif

        <div class="border rounded p-3 mb-3" style="background: var(--bs-tertiary-bg, #f8f9fa);">
            @php $periods = $price->periods(); $labels = ['onetime' => 'one-time', 'monthly' => 'per month', 'yearly' => 'per year']; @endphp
            @forelse($periods as $period => $amount)
                <div class="d-flex align-items-baseline gap-2 {{ $loop->first ? '' : 'mt-1' }}">
                    <span class="{{ $loop->first ? 'fs-2' : 'fs-5' }} fw-bold">{{ number_format($amount, 2) }} {{ Cart::currency() }}</span>
                    <span class="text-muted">{{ $labels[$period] }}</span>
                </div>
            @empty
                <div class="fs-5 text-muted">Contact us for a quote</div>
            @endforelse
            <div class="small text-muted mt-1">Taxes estimated in the cart from your billing address.</div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach($periods as $period => $amount)
                    <x-rpd::button :label="$period === 'onetime' ? 'Add to cart' : 'Subscribe ' . $labels[$period]" icon="cart-plus"
                                   :color="$loop->first ? 'primary' : 'outline-primary'" click="dispatchSelf('addToCart', { period: '{{ $period }}' })" />
                @endforeach
                @if(Cart::count() > 0)
                    <a href="{{ route('shop.cart') }}" class="btn btn-outline-secondary"><i class="fas fa-shopping-cart me-1"></i> Go to cart ({{ Cart::count() }})</a>
                @endif
            </div>
        </div>

        @if($price->product->type === 'inventory_item')
            <div class="small text-muted"><i class="fas fa-truck me-1"></i> Physical item: shipped to the address you choose at checkout.</div>
        @else
            <div class="small text-muted"><i class="fas fa-bolt me-1"></i> Delivered online, no shipping.</div>
        @endif
    </div>

</div>
