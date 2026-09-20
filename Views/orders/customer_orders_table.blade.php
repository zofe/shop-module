<x-rpd::card title="My Orders">

    <table class="table">
        <thead>
        <tr>
            <th>{{ __('Order') }}</th>
            <th>{{ __('Products') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-end">{{ __('Total') }}</th>
            <th>{{ __('Date') }}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $order)
            <tr>
                <td class="small text-muted">{{ $order->shortId }}</td>
                <td>
                    @foreach ($order->items as $itm)
                        <div class="small">{{ $itm->name }}</div>
                    @endforeach
                </td>
                <td>
                    <span class="badge bg-{{ match($order->status) {
                        'new' => 'secondary',
                        'pending_payment' => 'warning',
                        'payment_done', 'in_process' => 'info',
                        'completed' => 'success',
                        'cancelled', 'payment_failed' => 'danger',
                        default => 'light'
                    } }}">{{ $order->status }}</span>
                </td>
                <td class="text-end">{{ $order->total }} {{ Cart::currency() }}</td>
                <td class="small">
                    <x-rpd::date-formatted :date="$order->created_at" />
                </td>
                <td class="text-end">
                    <x-rpd::icon name="edit" route="orders.view" :params="$order->id" />
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $items->links() }}

</x-rpd::card>
