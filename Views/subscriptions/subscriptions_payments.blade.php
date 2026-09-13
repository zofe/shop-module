
<x-rpd::card title="payments::payment.payments">

    <table class="table table-sm">
        <thead>
        <tr>
            <th class="text-uppercase">
                id
            </th>
            <th>description</th>
            @if(auth()->user()->hasRoleOrPermission('admin|administrative|edit subscriptions') )
            <th>gcl_payment_id</th>
            @endif
            <th>
                status
            </th>
            <th>
                date
            </th>
            <th class="text-right">
                subtotal
            </th>
            <th class="text-right">
                total
            </th>
        </tr>
        </thead>
        <tbody>

        @foreach ($items as $payment)
            <tr @if($payment->payment_type == 'split') class="payment-status-{{ $payment->status }}" style="opacity: 0.7"  @endif>
                <td class="small">
                    @if($payment->payment_type == 'split')
                        {{ $payment->shortId }}
                    @else
                        <a href="{{ route_lang('payments.payments.view',$payment->id) }}">{{ $payment->shortId }}</a>
                    @endif
                </td>
                <td class="small">{!! nl2br($payment->description) !!}</td>
                @if(auth()->user()->hasRoleOrPermission('admin|administrative|edit subscriptions') )
                <td class="small">
                    {{ $payment->gcl_payment_id??'-' }}
                </td>
                @endif
                <td class="small">
                    {{ $payment->status??'-' }}
                </td>
                <td class="small">
                    @if($payment->gcl_charge_date)
                        <div class="small">charge date: {{ \Carbon\Carbon::parse($payment->gcl_charge_date)->format('d/m/Y') }}</div>
                    @else
                        <div>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</div>
                    @endif

                </td>
                <td class="small text-right text-nowrap">
                    {{ amount_format($payment->subtotal) }} €
                </td>
                <td class="small text-right text-nowrap">
                    {{ amount_format($payment->total) }} €
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

    <div class="d-flex justify-content-between">
        <div class="form-inline">
            {{ $items->fragment('payments')->links() }}
        </div>
    </div>

</x-rpd::card>
