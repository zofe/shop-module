<x-rpd::card>

    <x-rpd::table
        title="Service Items"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <x-rpd::button label="Reset" route="service_items.table" color="outline-dark" />
        </x-slot>

        <table class="table">
            <thead>
            <tr>
                <th>{{ __('id') }}</th>
                <th>{{ __('service') }}</th>
                <th>{{ __('status') }}</th>
                <th>{{ __('owner') }}</th>
                <th>{{ __('sold by') }}</th>
                <th>{{ __('driver') }}</th>
                <th>{{ __('licence') }}</th>
                <th>{{ __('updated') }}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                @php($soldBy = $item->soldBy())
                <tr>
                    <td><x-rpd::nav-link :label="$item->shortId" route="service_items.edit" :params="$item->id" /></td>
                    <td>{{ optional($item->product)->name }}</td>
                    <td>
                        <span class="badge bg-{{ match($item->status) { 'active' => 'success', 'suspended' => 'warning text-dark', 'terminated' => 'danger', default => 'secondary' } }}">{{ $item->status }}</span>
                    </td>
                    <td>{{ optional($item->owner)->business_name ?? optional($item->owner)->name }}</td>
                    <td class="small">
                        @if($soldBy instanceof \App\Modules\Shop\Models\Order)
                            <x-rpd::nav-link icon="file-invoice" :label="'Order ' . $soldBy->shortId" route="orders.view" :params="$soldBy->id" />
                        @elseif($soldBy instanceof \App\Modules\Shop\Models\Subscription)
                            <x-rpd::nav-link icon="sync" :label="'Subscription ' . $soldBy->shortId" route="subscriptions.view" :params="$soldBy->id" />
                        @endif
                    </td>
                    <td class="small">{{ $item->provisionerName() }}</td>
                    <td class="small">
                        @if($item->license)
                            {{ $item->license->status }} → {{ optional($item->license->expire_date)->format('Y-m-d') ?? '∞' }}
                        @endif
                    </td>
                    <td class="small"><x-rpd::date-formatted :date="$item->updated_at"></x-rpd::date-formatted></td>
                    <td><x-rpd::icon name="edit" route="service_items.edit" :params="$item->id" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
