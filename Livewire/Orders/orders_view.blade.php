@php
$title = $product->exists ? 'Update Product/Service' : 'Create Product/Service';
@endphp
<x-rpd::card>
    <x-rpd::edit :title="$title">

        <x-slot name="buttons">
            <x-rpd::button label="Back" route="products.table" color="outline-dark" />
        </x-slot>

        <div class="row">

            <x-rpd::input col="col-md-4" model="product.name" label="Name" />
	        <x-rpd::input col="col-md-4" model="product.description" label="Description" />
            <x-rpd::input col="col-md-4" model="product.sku" label="Sku" />

        </div>

        <x-slot name="actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </x-slot>

    </x-rpd::edit>
</x-rpd::card>
