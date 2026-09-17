<x-rpd::view title="Subscription {{ $subscription->shortId }}">
    <x-slot name="buttons">
        <a href="{{ route('subscriptions.table') }}" class="btn btn-outline-primary">List</a>
    </x-slot>

    <div class="row">
        <div class="col-md-8">

            <x-rpd::card title="Subscription items">
                <x-slot name="buttons">
                    @if(! in_array($subscription->status, ['cancelled']))
                        <x-rpd::button size="sm" color="outline-primary" label="Add item" icon="plus" click="openLine()" />
                    @endif
                </x-slot>
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
                        <tr wire:key="si-{{ $item->id }}" class="{{ $item->bundle_code ? 'text-muted small' : '' }}">
                            <td>{{ $item->prd_code }}</td>
                            <td>{{ $item->bundle_code ? '↳ ' : '' }}{{ $item->name }}
                                @if(! $item->bundle_code)
                                    <x-rpd::icon name="edit" click="openLine({{ $item->id }})" />
                                    <x-rpd::icon name="trash-alt" click="removeItem({{ $item->id }})" confirm="Remove {{ $item->name }}?" />
                                @endif</td>
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

            @if($hasRecorder)
                <x-rpd::card title="Payments">
                    @if(count($payments) === 0)
                        <div class="text-muted small">No payment recorded yet.</div>
                    @else
                        <table class="table table-sm">
                            <thead><tr><th>Payment</th><th>Description</th><th>Gateway</th><th>Status</th><th>Date</th><th class="text-end">Total</th><th></th></tr></thead>
                            <tbody>
                            @foreach($payments as $payment)
                                <tr wire:key="sp-{{ $payment->id }}">
                                    <td>@if(Route::has('payments.view'))<a href="{{ route('payments.view', $payment) }}">{{ $payment->shortId ?? substr($payment->id, 0, 8) }}</a>@else{{ substr($payment->id, 0, 8) }}@endif
                                        @if($payment->ref_subscription_id)<span class="badge bg-light text-dark">renewal</span>@else<span class="badge bg-light text-dark">first</span>@endif</td>
                                    <td class="small">{{ $payment->description }}</td>
                                    <td>{{ $payment->gateway ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $payment->status === 'confirmed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning text-dark') }}">{{ $payment->status }}</span></td>
                                    <td>{{ ($payment->payment_date ?? $payment->created_at)?->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ number_format($payment->total, 2) }} {{ Cart::currency() }}</td>
                                    <td class="text-end text-nowrap">
                                        @if($payment->status === 'pending')
                                            <x-rpd::button size="xsm" color="outline-success" label="mark paid" click="markPaid('{{ $payment->id }}')" />
                                            <x-rpd::button size="xsm" color="outline-danger" label="failed" click="markFailed('{{ $payment->id }}')" />
                                        @endif
                                    </td>
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
                    @if($subscription->trial_ends_at)
                        <dt class="col-5">Trial ends</dt>
                        <dd class="col-7">{{ $subscription->trial_ends_at->format('Y-m-d') }}</dd>
                    @endif
                    <dt class="col-5">Next billing</dt>
                    <dd class="col-7">{{ $subscription->next_billing_at?->format('Y-m-d') ?? '—' }}</dd>
                    @if($subscription->ends_at)
                        <dt class="col-5">Ends</dt>
                        <dd class="col-7">{{ $subscription->ends_at->format('Y-m-d') }}</dd>
                    @endif
                    <dt class="col-5">Collected by</dt>
                    <dd class="col-7">{{ $subscription->gateway ?? '—' }}@if($subscription->gateway_ref)<div class="small text-muted">{{ $subscription->gateway_ref }}</div>@endif</dd>
                </dl>

                @if($hasRecorder && ! $pending && $subscription->isActive())
                    <x-rpd::button size="sm" color="outline-primary" label="Bill the next period now" icon="file-invoice-dollar" click="billNow" />
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


    {{-- add a fee, or change the quantity / variant of a line (prices from the subscription's price list) --}}
    <x-rpd::modal name="subscriptionLine" :title="$editingId ? 'Edit item' : 'Add item'" action="saveLine" :actionLabel="$editingId ? 'Save' : 'Add'" width="md">
        <div class="row g-2">
            @if($editingId)
                <div class="col-12 small text-muted">{{ optional($subscription->items->firstWhere('id', $editingId))->name }}</div>
            @else
                <x-rpd::select-list col="col-12" model="lineProduct" :options="$lineProducts" label="Product / service" placeholder="Choose…" />
            @endif
            @if(count($lineVariants))
                <x-rpd::select col="col-md-8" model="lineVariant" :options="$lineVariants" label="Variant" addempty />
            @endif
            <x-rpd::input col="col-md-4" model="lineQty" type="number" label="Quantity" />
        </div>
    </x-rpd::modal>
</x-rpd::view>
