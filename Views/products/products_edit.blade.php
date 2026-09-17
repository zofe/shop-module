@php
$title = $product->exists ? 'Update Product/Service' : 'Create Product/Service';
@endphp
<x-rpd::card>
    <x-rpd::edit :title="$title">

        <x-slot name="buttons">
            <x-rpd::button label="Back" route="products.table" color="outline-dark" />
        </x-slot>

        <div class="row">
            <x-rpd::select col="col-md-4" model="product.type" label="Type" :options="$types" addempty />
            <x-rpd::select-list col="col-md-4" model="product.category_id" :options="$availableCategories" label="Category" />
            <x-rpd::input col="col-md-4" model="product.sku" label="Sku" />
        </div>

        @if(($product->type ?? null) !== 'inventory_item')
            <div class="row">
                <x-rpd::select col="col-md-4" model="product.provisioner" label="Provisioning driver" :options="$provisioners" addempty />
                <x-rpd::select col="col-md-4" model="product.activation" label="Activation" :options="['automatic' => 'Automatic (at payment)', 'manual' => 'Manual (by the operator)', 'customer' => 'By the customer (licence key)']" addempty />
                <div class="col-md-4 small text-muted align-self-end pb-2">Empty = the shop's defaults (<code>shop.provisioning</code>). "By the customer" fits B2B: sold to a reseller, activated by the end user with the key.</div>
            </div>
        @endif

        <div class="row">
            <x-rpd::input col="col-md-12" model="product.name" label="Name" />
            <x-rpd::rich-text col="col-md-12 mt-2" model="product.description" label="Description" />
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Image</label>
                <x-rpd::upload model="image" col="" />

                @if($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail" style="max-height:160px;" alt="preview">
                    </div>
                @elseif($product->image_path)
                    <div class="mt-2 d-flex align-items-center gap-3">
                        <img src="{{ Storage::url($product->image_path) }}" class="img-thumbnail" style="max-height:160px;" alt="{{ $product->name }}">
                        <button type="button" wire:click="removeImage" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <x-slot name="actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </x-slot>

    </x-rpd::edit>
</x-rpd::card>
