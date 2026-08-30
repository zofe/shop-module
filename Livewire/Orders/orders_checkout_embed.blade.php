<div>
    @if(session('checkout_message'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('checkout_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($order->status !== 'pending_payment')
        <div class="alert alert-warning mb-0">
            This order is not awaiting payment (current status: <strong>{{ $order->status }}</strong>).
        </div>
    @else

        <x-rpd::card title="Order Detail">
            <table class="table">
                <thead>
                <tr>
                    <th>SKU</th>
                    <th>Description</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->prd_code }}</td>
                        <td>{{ $item->name }}</td>
                        <td class="text-end">{{ $item->price }} {{ Cart::currency() }}</td>
                        <td class="text-end">{{ $item->qty }}</td>
                        <td class="text-end">{{ $item->subtotal }} {{ Cart::currency() }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr class="tr-small">
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end">Subtotal</td>
                    <td class="text-end">{{ $order->subtotal }} {{ Cart::currency() }}</td>
                </tr>
                <tr class="tr-small">
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end">Shipping</td>
                    <td class="text-end shipping">{{ $order->shipping }} {{ Cart::currency() }}</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end">Tax</td>
                    <td class="text-end tax">{{ $order->tax }} {{ Cart::currency() }}</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end h5"><strong>Total</strong></td>
                    <td class="text-end h5 total"><strong>{{ $order->total }} {{ Cart::currency() }}</strong></td>
                </tr>
                </tfoot>
            </table>
        </x-rpd::card>

        <x-rpd::card title="Choose a payment method" class="mt-3">
            <div class="d-grid gap-2">
                @forelse($gateways as $key => $gateway)
                    <button
                        type="button"
                        class="btn btn-outline-primary d-flex align-items-center gap-3 py-3 px-4 text-start"
                        wire:click="initiatePayment('{{ $key }}')"
                        wire:loading.attr="disabled"
                        wire:target="initiatePayment('{{ $key }}')"
                    >
                        <i class="fas {{ $gateway['icon'] }} fa-lg text-primary" style="width:24px"></i>
                        <div>
                            <div class="fw-semibold">{{ $gateway['label'] }}</div>
                            <div class="small text-muted">{{ $gateway['description'] }}</div>
                        </div>
                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                    </button>
                @empty
                    <p class="text-muted small mb-0">No payment methods configured.</p>
                @endforelse
            </div>
        </x-rpd::card>

    @endif
</div>
