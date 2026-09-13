<div>

    @slot('buttons')
        <x-rpd::button
            label="Add Product/Service"
            color="outline-primary"
            click="$dispatch('editOrderItem')"
            size="sm"
            class="m-1"
        />
    @endslot

<x-rpd::modal
    name="editItem"
    title="Edit Order Item"
    action="saveItem"
>
    <div>
        <div class="row">
            <x-rpd::select-list inline="true" model="new_item" label="Product/Service" endpoint="/ajax/available-pricelist-item"  />
        </div>
        <div class="row">
            <x-rpd::input col="col-md-3" model="prd_code" label="Code" />
            <x-rpd::input col="col-9" model="name" label="Description" />

        </div>
        <x-rpd::input inline model="price" label="Price" />
        <x-rpd::input inline model="qty" label="Qty" />
        <x-rpd::input inline model="shipping" label="Shipping" />
    </div>
</x-rpd::modal>

</div>

