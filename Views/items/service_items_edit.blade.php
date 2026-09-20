@php
    $soldBy = $item->exists ? $item->soldBy() : null;
@endphp
<div class="row">
    <div class="col-md-8">
        <x-rpd::card title="Service item {{ $item->shortId }}">
            <x-slot name="buttons">
                @include('shop::includes.documents', ['subject' => $item])
                <x-rpd::button label="Back" route="service_items.table" color="outline-dark" />
            </x-slot>

            <dl class="row mb-0">
                <dt class="col-4">{{ __('Service') }}</dt>
                <dd class="col-8">{{ optional($item->product)->name }}</dd>

                <dt class="col-4">{{ __('Owner') }}</dt>
                <dd class="col-8">{{ optional($item->owner)->business_name ?? optional($item->owner)->name ?? '—' }}</dd>

                <dt class="col-4">{{ __('Sold by') }}</dt>
                <dd class="col-8">
                    @if($soldBy instanceof \App\Modules\Shop\Models\Order)
                        <x-rpd::nav-link icon="file-invoice" :label="'Order ' . $soldBy->shortId" route="orders.view" :params="$soldBy->id" />
                    @elseif($soldBy instanceof \App\Modules\Shop\Models\Subscription)
                        <x-rpd::nav-link icon="sync" :label="'Subscription ' . $soldBy->shortId" route="subscriptions.view" :params="$soldBy->id" />
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-4">{{ __('Driver') }}</dt>
                <dd class="col-8">{{ $item->provisionerName() }} @if($item->external_ref)<span class="text-muted small">{{ __('ref.') }} {{ $item->external_ref }}</span>@endif</dd>

                <dt class="col-4">{{ __('Licence') }}</dt>
                <dd class="col-8">
                    @if($item->license)
                        {{ $item->license->shortId }} · {{ $item->license->status }} ·
                        {{ optional($item->license->activation_date)->format('Y-m-d') ?? 'not activated' }} → {{ optional($item->license->expire_date)->format('Y-m-d') ?? 'no expiry' }}
                        @if($item->license->key)
                            <div class="font-monospace small mt-1">{{ $item->license->key }}</div>
                        @endif
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-4">{{ __('Activation') }}</dt>
                <dd class="col-8">{{ optional($item->product)->activationPolicy() ?? '—' }}</dd>

                @if($item->metadata)
                    <dt class="col-4">{{ __('Driver data') }}</dt>
                    <dd class="col-8"><pre class="small mb-0">{{ json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></dd>
                @endif
            </dl>
        </x-rpd::card>
    </div>
    <div class="col-md-4">
        <x-rpd::card title="Status">
            <dl class="row">
                <dt class="col-4">{{ __('Status') }}</dt>
                <dd class="col-8">{{ $item->status }}</dd>
            </dl>
            @if($item->exists)
                <livewire:workflow::workflow-table-embed
                    workfloable-type="service_item"
                    workfloable-id="{{ $item->id }}"
                    editable="true"
                    showHistory="true"
                />
            @endif
        </x-rpd::card>
    </div>
</div>
