<x-rpd::card>

    <x-rpd::view :title="$priceList->is_default ? 'Default Price list' : 'Price List Detail'">

        <x-slot name="buttons">

            <x-rpd::button
                label="Add Product"
                color="outline-primary"
                click="$dispatch('addPriceListItem',{priceListId: {{$priceList->id}}})"
            />
            <x-rpd::button
                label="Edit"
                color="outline-primary"
                click="$dispatch('editPriceList',{priceListId: '{{$priceList->id}}'})"
            />
            <x-rpd::button
                label="Delete"
                color="outline-danger"
                click="$dispatch('deletePriceList',{priceListId: '{{$priceList->id}}'})"
                confirm="delete price list {{ $priceList->name }}?"
            />

        </x-slot>

        <div>
            <h4>Pricelist : {{ $priceList->name }}</h4>

        </div>

        <livewire:shop::prices-price-lists-modal-edit-embed />


        <div>
            <livewire:shop::prices-price-lists-item-edit-embed  :key="'new'" />
        </div>

        <div class="border-bottom-except-last">
            @foreach($priceList->items as $item)
                <livewire:shop::prices-price-lists-item-edit-embed :priceListItem="$item" :key="$item->id" />
            @endforeach
        </div>


    </x-rpd::view>
</x-rpd::card>
