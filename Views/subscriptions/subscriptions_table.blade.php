<div>
    <x-rpd::card>
        <x-rpd::table title="Subscriptions" :items="$items">
            <x-slot name="filters">
                <x-rpd::input col="col-8" model="search" placeholder="search..." />
            </x-slot>
            <table class="table table-sm">
                <thead>
                <tr>
                    <th class="text-uppercase">id</th>
                    <th>description</th>
                    <th>customer</th>
                    <th>period</th>
                    <th>status</th>
                    <th>next billing</th>
                    <th>collected by</th>
                    <th class="text-end">fee</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($items as $subscription)
                    <tr wire:key="s-{{ $subscription->id }}">
                        <td><a href="{{ route('subscriptions.view', $subscription) }}">{{ $subscription->shortId }}</a></td>
                        <td>{{ $subscription->description }}</td>
                        <td>
                            @if($subscription->company){{ $subscription->company->business_name }}
                            @elseif($subscription->user){{ $subscription->user->name }}@endif
                        </td>
                        <td>{{ $subscription->period }}</td>
                        <td>{{ $subscription->status }}</td>
                        <td>{{ $subscription->next_billing_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $subscription->gateway ?? '—' }}</td>
                        <td class="text-end text-nowrap">{{ number_format($subscription->total, 2) }} {{ Cart::currency() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-rpd::table>
    </x-rpd::card>
</div>
