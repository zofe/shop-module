@php
$title = $product->exists ? 'Update Product/Service' : 'Create Product/Service';
@endphp
<x-rpd::card>
    <x-rpd::edit :title="$title">

        <x-slot name="buttons">
            <x-rpd::button label="Back" route="products.table" color="outline-dark" />
        </x-slot>

        <div class="row">

            <div class="col-md-6">
                <x-rpd::input model="product.name" label="Name" />
                <x-rpd::input model="product.description" label="Description" />
            </div>
            <div class="col-md-6">
                <x-rpd::input model="product.sku" label="Sku" />
                <x-rpd::select-list model="product.category_id" :options="$availableCategories" label="Category" />
            </div>



        </div>

        <x-slot name="actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </x-slot>

    </x-rpd::edit>
</x-rpd::card>
