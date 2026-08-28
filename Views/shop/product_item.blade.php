<div class="col-12 col-sm-6 col-md-6 mb-3">
    <div class="card h-100 shadow-sm">
        @if($price->product->image_path)
            <a href="{{ route('shop.list', $price->product->full_path) }}">
                <img src="{{ Storage::url($price->product->thumb_path) }}"
                     class="card-img-top"
                     style="height:180px; object-fit:cover;"
                     alt="{{ $price->product->name }}">
            </a>
        @endif
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $price->product->name }}</h5>
            @if($price->product->description)
                <p class="card-text text-muted small flex-grow-1">{!! Str::limit(strip_tags($price->product->description), 120) !!}</p>
            @endif
            <div class="d-flex align-items-center justify-content-between mt-3">
                @if($price->price_onetime_customer > 0)
                    <span class="fw-bold">{{ number_format($price->price_onetime_customer, 2) }} {{ Cart::currency() }}</span>
                @elseif($price->price_monthly_customer > 0)
                    <span class="fw-bold">{{ number_format($price->price_monthly_customer, 2) }} {{ Cart::currency() }}<small class="text-muted fw-normal">/mo</small></span>
                @else
                    <span class="text-muted small">Contact us</span>
                @endif
                <a href="{{ route('shop.list', $price->product->full_path) }}" class="btn btn-outline-primary btn-sm">View</a>
            </div>
        </div>
    </div>
</div>
