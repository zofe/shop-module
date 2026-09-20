
<x-rpd::card>

    <x-rpd::table
        title="Price Lists"
        :items="$items"
    >

        <x-slot name="buttons">

            <x-rpd::button
                label="Add"
                color="outline-primary"
                click="$dispatch('editPriceList')"
            />

        </x-slot>


        <table class="table">
            <thead>
            <tr>
                <th>
                    <x-rpd::sort model="name" label="name" />
                </th>
                <th>{{ __('active') }}</th>
                <th>{{ __('default') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $priceList)
                <tr>
                    <td>
                        <a href="{{ route_lang('price_lists.view', $priceList->id ) }}"> {{ $priceList->name }}</a>
                    </td>
                    <td>@if($priceList->is_active) <i class="fas fa-check text-success"></i> @else @endif</td>
                    <td>@if($priceList->is_default) <i class="fas fa-circle text-success"></i>@else @endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>





    </x-rpd::table>
</x-rpd::card>
