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
                <th>
                    <x-rpd::sort model="id" label="id" />
                </th>
                <th>serial_number</th>
                <th>product</th>
                <th>status</th>
                <th>owner</th>
                <th>created_at</th>
                <th>updated_at</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route_lang('inventory_items.edit', $item->id ) }}">{{ $item->id }}</a>
                    </td>
                    <td>{{ $item->serial_number }} </td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->status }} </td>
                    <td>{{ optional($item->owner)->name }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
