<x-rpd::card>

    <x-rpd::table
        title="Inventory Items"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <x-rpd::button label="Reset" route="inventory_items.table" color="outline-dark" />
            <x-rpd::button label="Add" route="inventory_items.edit" color="outline-primary" />
        </x-slot>


        <table class="table">
            <thead>
            <tr>
                <th>{{ __('Id') }}</th>
                <th>{{ __('Serial number') }}</th>
                <th>{{ __('product') }}</th>
                <th>{{ __('status') }}</th>
                <th>{{ __('owner') }}</th>
                <th>{{ __('Created at') }}</th>
                <th>{{ __('Updated at') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        <x-rpd::nav-link :label="$item->shortId" route="inventory_items.edit" :params="$item->id" />
                    </td>
                    <td>{{ $item->serial_number }} </td>
                    <td>{{ optional($item->product)->name }}</td>
                    <td>{{ $item->status }} </td>
                    <td>{{ optional($item->owner)->name }}</td>
                    <td class="small">
                        <x-rpd::date-formatted :date="$item->created_at"></x-rpd::date-formatted>
                    </td>
                    <td>
                        <x-rpd::date-formatted :date="$item->updated_at"></x-rpd::date-formatted>
                    </td>
                    <td>
                        <x-rpd::icon name="edit" route="inventory_items.edit" :params="$item->id" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
