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
            <x-rpd::button label="Add" route="service_items.edit" color="outline-primary" />
        </x-slot>


        <table class="table">
            <thead>
            <tr>
                <th>
                   id
                </th>
                <th>service</th>
                <th>status</th>
                <th>owner</th>
                <th>license or subscr.</th>
                <th>activation</th>
                <th>expiration</th>
                <th>created_at</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>
                        <x-rpd::nav-link :label="$item->shortId" route="service_items.edit" :params="$item->id" />
{{--                        <a href="{{ route_lang('service_items.edit', $item->id ) }}">{{ $item->id }}</a>--}}
                    </td>
                    <td>{{ $item->product->name }} </td>
                    <td>{{ $item->status }} </td>
                    <td>{{ optional($item->owner)->name }}</td>
                    <td>{{ $item->license }} {{ $item->subscription }}</td>
                    <td>{{ $item->activation_date }}</td>
                    <td>{{ $item->expiration_date }}</td>
                    <td>{{ $item->updated_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
