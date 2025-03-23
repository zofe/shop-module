<x-rpd::card>

    <x-rpd::table
        title="Products & Services"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <x-rpd::button label="Reset" route="products.table" color="outline-dark" />
            <x-rpd::button label="Add" route="products.edit" color="outline-primary" />
        </x-slot>


        <table class="table">
            <thead>
            <tr>
                <th>
                    <x-rpd::sort model="id" label="id" />
                </th>
                <th>type</th>
                <th>name</th>
                <th>sku</th>
                <th>category</th>
                <th>created_at</th>
                <th>updated_at</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $product)
                <tr>
                    <td>
                        <a href="{{ route_lang('products.edit', $product->id ) }}">{{ $product->id }}</a>
                    </td>
                    <td>{{ $product->type }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }} </td>
                    <td>{{ optional($product->category)->name }}</td>
                    <td class="small">{{ $product->created_at }}</td>
                    <td class="small">{{ $product->updated_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
