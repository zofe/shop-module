<div class="row justify-content-center">
    <div class="col-md-7">
        <livewire:shop::orders.orders-checkout-embed :order="$order" />

        <div class="mt-2">
            <a href="{{ route('orders.view', $order) }}" class="btn btn-link btn-sm text-muted">
                ← Back to order
            </a>
        </div>
    </div>
</div>
