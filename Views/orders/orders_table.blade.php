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
                    id
                </th>
                <th>products</th>
                <th>status</th>
                <th>customer</th>
                <th>subtotal</th>
                <th><x-rpd::sort model="created_at" label="created_at" /></th>
                <th><x-rpd::sort model="updated_at" label="updated_at" /></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $order)
                <tr>
                    <td>
                        <x-rpd::nav-link :label="$order->shortId" route="orders.view" :params="$order->id" />
                    </td>
                    <td>
                         @foreach ($order->items->sortByDesc('deliverable_type') as $itm)
                            @if($itm->deliverable_type)
                                <span class="badge bg-primary position-relative">
                                    {{ $itm->name }}
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info"> {{ round($itm->qty) }}</span>
                                </span>
                            @else
                                <span class="small">
                                    {{ Str::limit( $itm->name, '10') }}
                                </span>
                            @endif

                         @endforeach
                    </td>
                    <td>
                        {{ $order->status }}
                    </td>
                    <td>
                        @if($order->company)
                            {{ $order->company->name }}
                        @elseif($order->user)
                            {{ $order->user->name }}
                        @endif
                    </td>
                    <td class="text-end">
                        {{ $order->subtotal }} {{ Cart::currency() }}
                    </td>
                    <td class="small">
                        <x-rpd::date-formatted :date="$order->created_at"></x-rpd::date-formatted>
                    </td>
                    <td>
                        <x-rpd::date-formatted :date="$order->updated_at"></x-rpd::date-formatted>
                    </td>
                    <td>
                        <x-rpd::icon name="edit" route="orders.view" :params="$order->id" />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
