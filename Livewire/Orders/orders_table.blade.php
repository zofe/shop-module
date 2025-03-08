<x-rpd::card>

    <x-rpd::table
        title="Orders"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <x-rpd::button label="Reset" route="orders.table" color="outline-dark" />
        </x-slot>


        <table class="table">
            <thead>
            <tr>
                <th>
                    <x-rpd::sort model="id" label="id" />
                </th>
{{--                <th>sku</th>--}}
{{--                <th>description</th>--}}
{{--                <th>created_at</th>--}}
{{--                <th>updated_at</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $order)
                <tr>
                    <td>
                        <a href="{{ route_lang('orders.view', $order->id ) }}">{{ $order->shortId }}</a>
                    </td>
{{--                    <td>{{ $product->sku }} </td>--}}
{{--                    <td>{{ $product->description }}</td>--}}
{{--                    <td>{{ $product->created_at }}</td>--}}
{{--                    <td>{{ $product->updated_at }}</td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
