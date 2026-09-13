@php
$title = $item->exists ? 'Update Service Item' : 'Create Service Item';
@endphp
<x-rpd::card>
    <x-rpd::edit :title="$title">

        <x-slot name="buttons">
            <x-rpd::button label="Back" route="service_items.table" color="outline-dark" />
        </x-slot>

        <div class="row">
{{--            <x-rpd::input col="col-md-4" model="item.serial_number" label="Name" />--}}
        </div>

        <x-slot name="actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </x-slot>

    </x-rpd::edit>
</x-rpd::card>
