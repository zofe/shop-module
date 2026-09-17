<div>
    <div class="row g-4">
        <div class="col-md-4">
            <x-rpd::card title="Status">
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt><dd class="col-7">{{ $subscription->status }}</dd>
                    <dt class="col-5">Period</dt><dd class="col-7">{{ $subscription->period }}</dd>
                    <dt class="col-5">Started</dt><dd class="col-7">{{ $subscription->start_date?->format('Y-m-d') }}</dd>
                    @if($subscription->trial_ends_at)<dt class="col-5">Trial ends</dt><dd class="col-7">{{ $subscription->trial_ends_at->format('Y-m-d') }}</dd>@endif
                    <dt class="col-5">Next billing</dt><dd class="col-7">{{ $subscription->next_billing_at?->format('Y-m-d') ?? '—' }}</dd>
                    <dt class="col-5">Fee</dt><dd class="col-7">{{ number_format($subscription->total, 2) }} {{ Cart::currency() }} / {{ $subscription->period === 'yearly' ? 'year' : 'month' }}</dd>
                </dl>
            </x-rpd::card>
        </div>

        <div class="col-md-8">
            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>

            @if(session('checkout_message'))
                <div class="alert alert-info">{{ session('checkout_message') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($pending || (! $hasRecorder && in_array($subscription->status, ['pending'])))
                <x-rpd::card title="Payment due">
                    <p class="mb-2">{{ $pending->description ?? $subscription->payableDescription() }}:
                        <strong>{{ number_format($pending->total ?? $subscription->payableAmounts()['total'], 2) }} {{ Cart::currency() }}</strong>
                        @if($pending?->gateway)<span class="badge bg-warning text-dark ms-1">{{ $pending->gateway }} · {{ $pending->status }}</span>@endif
                    </p>
                    @if($methods->isNotEmpty())
                        <div class="d-grid gap-2">
                            @foreach($methods as $key => $method)
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-3 py-2 px-3 text-start"
                                        wire:click="pay('{{ $key }}')" wire:loading.attr="disabled">
                                    <i class="fas {{ $method->icon() }} fa-lg text-primary" style="width:24px"></i>
                                    <div><div class="fw-semibold">{{ $method->label() }}</div><div class="small text-muted">{{ $method->description() }}</div></div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">We will contact you for the payment.</div>
                    @endif
                </x-rpd::card>
            @endif

            <x-rpd::card title="Subscription items">
                <x-slot name="buttons">
                    @include('shop::includes.documents', ['subject' => $subscription])
                </x-slot>
                <table class="table table-sm">
                    <thead><tr><th>SKU</th><th>Description</th><th class="text-end">Qty</th><th class="text-end">Fee</th></tr></thead>
                    <tbody>
                    @foreach($subscription->items as $item)
                        <tr wire:key="si-{{ $item->id }}" class="{{ $item->bundle_code ? 'text-muted small' : '' }}">
                            <td>{{ $item->prd_code }}</td>
                            <td>{{ $item->bundle_code ? '↳ ' : '' }}{{ $item->name }}</td>
                            <td class="text-end">{{ $item->qty }}</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }} {{ Cart::currency() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr><td colspan="3" class="text-end"><strong>Total / {{ $subscription->period === 'yearly' ? 'year' : 'month' }}</strong></td><td class="text-end"><strong>{{ number_format($subscription->total, 2) }} {{ Cart::currency() }}</strong></td></tr>
                    </tfoot>
                </table>
            </x-rpd::card>

            @if($hasRecorder)
                <x-rpd::card title="Payments">
                    @if(count($payments) === 0)
                        <div class="text-muted small">No payment yet.</div>
                    @else
                        <table class="table table-sm">
                            <thead><tr><th>Description</th><th>Status</th><th>Date</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                            @foreach($payments as $payment)
                                <tr wire:key="p-{{ $payment->id }}">
                                    <td class="small">{{ $payment->description }}</td>
                                    <td><span class="badge bg-{{ $payment->status === 'confirmed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning text-dark') }}">{{ $payment->status }}</span> <span class="small text-muted">{{ $payment->gateway }}</span></td>
                                    <td>{{ ($payment->payment_date ?? $payment->created_at)?->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ number_format($payment->total, 2) }} {{ Cart::currency() }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-rpd::card>
            @endif
        </div>
    </div>
</div>
