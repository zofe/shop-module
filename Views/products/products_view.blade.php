<div class="row">
    <div class="col-md-6">

        <x-rpd::card title="Product/Service Detail">

            <x-slot name="buttons">
                <a href="{{ route_lang('products.table') }}" class="btn btn-outline-dark">{{ __('Back') }}</a>
                <a href="{{ route_lang('products.edit', $product->id) }}" class="btn btn-outline-primary">{{ __('Edit') }}</a>
            </x-slot>

            @if($product->image_path)
                <div class="mb-3">
                    <img src="{{ Storage::url($product->image_path) }}"
                         class="img-fluid rounded"
                         style="max-height:220px; object-fit:cover; width:100%;"
                         alt="{{ $product->name }}">
                </div>
            @endif

            <dl class="row mb-0">
                <dt class="col-5">{{ __('Type') }}</dt>
                <dd class="col-7">{{ $product->type }}</dd>
                <dt class="col-5">{{ __('Name') }}</dt>
                <dd class="col-7">{{ $product->name }}</dd>
                <dt class="col-5">{{ __('SKU') }}</dt>
                <dd class="col-7">{{ $product->sku }}</dd>
                <dt class="col-5">{{ __('Category') }}</dt>
                <dd class="col-7">{{ optional($product->category)->name }}</dd>
                @if($product->description)
                    <dt class="col-5">{{ __('Description') }}</dt>
                    <dd class="col-7">{!! $product->description !!}</dd>
                @endif
            </dl>

        </x-rpd::card>

    </div>
    <div class="col-md-6">

        <x-rpd::card title="Variants">
            <livewire:shop::products.products-variants-table-embed
                :product="$product->id"
                editable="true"
            />
        </x-rpd::card>

    </div>
</div>
