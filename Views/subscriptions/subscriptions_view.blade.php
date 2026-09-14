<x-rpd::view title="Subscription {{ $subscription->shortId }}">
    <x-slot name="buttons">
        <a href="{{ route('subscriptions.table') }}" class="btn btn-outline-primary">List</a>
    </x-slot>

    <div class="row">
        <div class="col-md-8">

            <x-rpd::card title="Subscription items">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Description</th>
                        <th>Period</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscription->items as $item)
                        <tr wire:key="si-{{ $item->id }}">
                            <td>{{ $item->prd_code }}</td>
                            <td>{{ $item->name }}</td>
                            <td><span class="badge bg-secondary">{{ $item->period }}</span></td>
                            <td class="text-end">{{ number_format($item->price, 2) }} {{ Cart::currency() }}</td>
                            <td class="text-end">{{ $item->qty }}</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }} {{ Cart::currency() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="tr-small"><td colspan="4"></td><td class="text-end">Subtotal</td><td class="text-end">{{ number_format($subscription->subtotal, 2) }} {{ Cart::currency() }}</td></tr>
                    <tr class="tr-small"><td colspan="4"></td><td class="text-end">Tax</td><td class="text-end">{{ number_format($subscription->tax, 2) }} {{ Cart::currency() }}</td></tr>
                    <tr><td colspan="4"></td><td class="text-end"><strong>Total / {{ $subscription->period === 'yearly' ? 'year' : 'month' }}</strong></td><td class="text-end"><strong>{{ number_format($subscription->total, 2) }} {{ Cart::currency() }}</strong></td></tr>
                    </tfoot>
                </table>
            </x-rpd::card>

            <x-rpd::card title="Orders">
                <table class="table table-sm">
                    <thead><tr><th>Order</th><th>Kind</th><th>Status</th><th>Created</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    @foreach(collect([$subscription->order])->filter()->merge($subscription->renewals) as $order)
                        <tr wire:key="so-{{ $order->id }}">
                            <td><a href="{{ route('orders.view', $order) }}">{{ $order->shortId }}</a></td>
                            <td>{{ $order->kind }}</td>
                            <td>{{ $order->status }}</td>
                            <td><x-rpd::date-formatted :date="$order->created_at" /></td>
                            <td class="text-end">{{ number_format($order->total, 2) }} {{ Cart::currency() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </x-rpd::card>

            @if($hasPayments)
                <x-rpd::card title="Payments">
                    @if($payments->isEmpty())
                        <div class="text-muted small">No payment recorded yet.</div>
                    @else
                        <table class="table table-sm">
                            <thead><tr><th>Payment</th><th>Description</th><th>Gateway</th><th>Status</th><th>Date</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                            @foreach($payments as $payment)
                                <tr wire:key="sp-{{ $payment->id }}">
                                    <td>@if(Route::has('payments.view'))<a href="{{ route('payments.view', $payment) }}">{{ $payment->shortId ?? substr($payment->id, 0, 8) }}</a>@else{{ substr($payment->id, 0, 8) }}@endif
                                        @if($payment->ref_subscription_id)<span class="badge bg-light text-dark">renewal</span>@endif</td>
                                    <td class="small">{{ $payment->description }}</td>
                                    <td>{{ $payment->gateway }}</td>
                                    <td>{{ $payment->status }}</td>
                                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? $payment->created_at->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ number_format($payment->total, 2) }} {{ Cart::currency() }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-rpd::card>
            @endif
        </div>

        <div class="col-md-4">
            <x-rpd::card title="Status">
                <dl class="row">
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">{{ $subscription->status }}</dd>
                    <dt class="col-5">Period</dt>
                    <dd class="col-7">{{ $subscription->period }}</dd>
                    <dt class="col-5">Started</dt>
                    <dd class="col-7">{{ $subscription->start_date?->format('Y-m-d') }}</dd>
                    <dt class="col-5">Next billing</dt>
                    <dd class="col-7">{{ $subscription->next_billing_at?->format('Y-m-d') ?? '—' }}</dd>
                    @if($subscription->ends_at)
                        <dt class="col-5">Ends</dt>
                        <dd class="col-7">{{ $subscription->ends_at->format('Y-m-d') }}</dd>
                    @endif
                    <dt class="col-5">Managed by</dt>
                    <dd class="col-7">
                        @if($subscription->isManagedByShop())
                            the shop <span class="text-muted small">(renewal orders)</span>
                        @else
                            <span class="badge bg-info text-dark">{{ $subscription->managed_by }}</span>
                            @if($subscription->gateway_ref)<div class="small text-muted">{{ $subscription->gateway_ref }}</div>@endif
                        @endif
                    </dd>
                </dl>

                @if($subscription->isManagedByShop() && $subscription->isActive())
                    <x-rpd::button size="sm" color="outline-primary" label="Renewal order now" icon="sync" click="renewNow" />
                @endif

                <livewire:workflow::workflow-table-embed
                    workfloable-type="subscription"
                    workfloable-id="{{ $subscription->id }}"
                    editable="true"
                    showHistory="true"
                />
            </x-rpd::card>

            <x-rpd::card title="Customer">
                <dl class="row">
                    @if($subscription->company)
                        <dt class="col-4">Company</dt>
                        <dd class="col-8"><x-rpd::nav-link icon="address-card" :label="$subscription->company->business_name" route="companies.view" :params="$subscription->company_id" /></dd>
                    @endif
                    @if($subscription->user)
                        <dt class="col-4">User</dt>
                        <dd class="col-8"><x-rpd::nav-link icon="user" :label="$subscription->user->name" route="auth.users.view" :params="$subscription->user_id" /></dd>
                    @endif
                </dl>
            </x-rpd::card>
        </div>
    </div>
</x-rpd::view>
