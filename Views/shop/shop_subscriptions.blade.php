<div>
    <div class="row g-4">
        <div class="col-md-4">
            @include('shop::includes.sidebar')
        </div>
        <div class="col-md-8">
            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>
            <x-rpd::card>
                <x-rpd::table title="My subscriptions" :items="$items">
                    <table class="table table-sm">
                        <thead><tr><th>id</th><th>description</th><th>period</th><th>status</th><th>next billing</th><th class="text-end">fee</th></tr></thead>
                        <tbody>
                        @foreach($items as $subscription)
                            <tr wire:key="s-{{ $subscription->id }}">
                                <td><a href="{{ route('shop.subscription', $subscription) }}">{{ $subscription->shortId }}</a></td>
                                <td>{{ $subscription->description }}</td>
                                <td>{{ $subscription->period }}</td>
                                <td>{{ $subscription->status }}</td>
                                <td>{{ $subscription->next_billing_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-end">{{ number_format($subscription->total, 2) }} {{ Cart::currency() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </x-rpd::table>
            </x-rpd::card>
        </div>
    </div>
</div>
