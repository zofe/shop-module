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
                <th></th>
                <th>type</th>
                <th>name</th>
                <th>sku</th>
                <th>variants</th>
                <th>category</th>
                <th>created_at</th>
                <th>updated_at</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $product)
                <tr>
                    <td>
                        <x-rpd::nav-link :label="$product->id" route="products.view" :params="$product->id" />
                    </td>
                    <td>
                        @if($product->image_path)
                            <img src="{{ Storage::url($product->thumb_path) }}"
                                 style="height:36px;width:36px;object-fit:cover;border-radius:4px;"
                                 alt="">
                        @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td>{{ $product->type }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }} </td>
                    <td>{{ \Illuminate\Support\Str::limit($product->variants()->pluck('name')->implode(', '),'50') }}</td>
                    <td>{{ optional($product->category)->name }}</td>
                    <td class="small">{{ $product->created_at }}</td>
                    <td class="small">{{ $product->updated_at }}</td>
                    <td>
                        <x-rpd::icon name="edit" route="products.view" :params="$product->id" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
