<div class="row">
    <div class="col-md-8">
        <x-rpd::card title="Order Detail">

            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th class="text-end">{{ __('Price') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Subtotal') }}</th>
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
                    <td class="text-end">{{ __('Subtotal') }}</td>
                    <td class="text-end">{{ $order->subtotal }} {{ Cart::currency() }}</td>
                </tr>
                <tr class="tr-small">
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end">{{ __('Shipping') }}</td>
                    <td class="text-end">{{ $order->shipping }} {{ Cart::currency() }}</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end">{{ __('Tax') }}</td>
                    <td class="text-end">{{ $order->tax }} {{ Cart::currency() }}</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-end h5"><strong>{{ __('Total') }}</strong></td>
                    <td class="text-end h5 total"><strong>{{ $order->total }} {{ Cart::currency() }}</strong></td>
                </tr>
                </tfoot>
            </table>

        </x-rpd::card>
    </div>

    <div class="col-md-4">
        <x-rpd::card title="Status">
            <dl class="row">
                <dt class="col-4">{{ __('Date') }}</dt>
                <dd class="col-8"><x-rpd::date-formatted :date="$order->created_at" /></dd>
                <dt class="col-4">{{ __('Status') }}</dt>
                <dd class="col-8">
                    <span class="badge bg-{{ match($order->status) {
                        'new' => 'secondary',
                        'pending_payment' => 'warning',
                        'payment_done', 'in_process' => 'info',
                        'completed' => 'success',
                        'cancelled', 'payment_failed' => 'danger',
                        default => 'light'
                    } }}">{{ $order->status }}</span>
                </dd>
            </dl>

            @if($order->status === 'pending_payment')
                <div class="mt-3">
                    <a href="{{ route_lang('orders.pay', $order) }}" class="btn btn-primary w-100">
                        {{ __('Pay now') }} — {{ $order->total }} {{ Cart::currency() }}
                    </a>
                </div>
            @endif
        </x-rpd::card>
    </div>
</div>
