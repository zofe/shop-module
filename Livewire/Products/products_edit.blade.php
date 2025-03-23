@php
$title = $product->exists ? 'Update Product/Service' : 'Create Product/Service';
@endphp
<x-rpd::card>
    <x-rpd::edit :title="$title">

        <x-slot name="buttons">
            <x-rpd::button label="Back" route="products.table" color="outline-dark" />
        </x-slot>

        <div class="row">
            <x-rpd::select col="col-md-4" model="product.type" label="Type" :options="$types"  addempty  />
            <x-rpd::select-list col="col-md-4" model="product.category_id" :options="$availableCategories" label="Category" />
            <x-rpd::input col="col-md-4" model="product.sku" label="Sku" />

        </div>
        <div class="row">
            <x-rpd::input col="col-md-12" model="product.name" label="Name" />
            <x-rpd::rich-text col="col-md-12 mt-2" model="product.description" label="Description" />
        </div>


        <x-slot name="actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </x-slot>

    </x-rpd::edit>
</x-rpd::card>
