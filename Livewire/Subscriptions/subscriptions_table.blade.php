<div>

    <x-rpd::card>
        <x-rpd::table
            title="Subscriptions"
            :items="$items"
        >
            <x-slot name="filters">
            </x-slot>

            <x-slot name="buttons">
            </x-slot>


            <table class="table table-sm">
                <thead>
                <tr>
                    <th class="text-uppercase">
                        id
                    </th>
                    <th>
                        description
                    </th>
                    <th>
                        customer
                    </th>
                    <th>
                        status
                    </th>
                    <th>
                        created_at
                    </th>
                    <th>
                        last payment
                    </th>
                    <th>
                        fee
                    </th>
                </tr>
                </thead>
                <tbody>
                @php /** @var $subscription \App\Models\Subscription */ @endphp
                @foreach ($items as $subscription)
                    <tr>
                        <td>
                            <a href="{{ route_lang('subscriptions.subscriptions.view', $subscription->id) }}">{{ $subscription->shortId }}</a>
                        </td>
                        <td>
                            {{ $subscription->computedDescription }}
                        </td>
                        <td>
                            @if($order->company)
                                {{ $order->company->name }}
                            @elseif($order->user)
                                {{ $order->user->name }}
                            @endif
                        </td>
                        <td>
                            {{ $subscription->status }}
                        </td>
                        <td>
                            {{ $subscription->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="small text-nowrap">
{{--                            @if($subscription->lastTransaction)--}}
{{--                                {{ $subscription->lastTransaction->created_at->format('d/m/Y') }}--}}
{{--                            @else--}}
{{--                                ---}}
{{--                            @endif--}}
                        </td>
                        <td>
{{--                            {{ $subscription->coin_total }} <i class="fas fa-coins"></i>--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>



        </x-rpd::table>
    </x-rpd::card>
</div>

