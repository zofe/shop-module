
<div class="col-12 col-sm-6 col-md-6 mb-3">
    <div class="card">
        <div class="card-body shadow">
            <h5 class="card-title">{{ $price->product->name }}</h5>
            <p class="card-text">{!! $price->product->description !!} </p>

            <a href="{{ route('shop.list', $price->product->full_path) }}" class="btn btn-outline-primary w-50 ms-auto d-block">view</a>
        </div>
    </div>
</div>



