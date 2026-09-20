<div class="row justify-content-center">
    <div class="col-md-7">
        <livewire:shop::orders.orders-checkout-embed :order="$order" />

        <div class="mt-2">
            <a href="{{ route_lang('orders.view', $order) }}" class="btn btn-link btn-sm text-muted">
                ← {{ __('Back to order') }}
            </a>
        </div>
    </div>
</div>
