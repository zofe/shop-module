<div>
    @slot('buttons')
        <x-rpd::button
            label="Add Variant"
            color="outline-primary"
            click="$dispatch('editVariant', {})"
        />
    @endslot

    @if($variants)
        <table class="table">
            <thead>
                <tr>
                    <th>name</th>
                    <th>meta</th>
                    <th>sku</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($variants as $variant)
                <tr>
                    <td>{{ $variant->name }}</td>
                    <td>
                        @if($variant->metadata)
                            @foreach($variant->metadata as $key => $val)
                                    <div class="small">
                                        {{ $key }}: {{ $val }}
                                    </div>
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $variant->sku }} </td>
                    <td>
                        <x-rpd::icon name="edit" click="$dispatch('editVariant',{variant: '{{$variant->id}}'})" />
                        @if($variants->count() > 1)
                            <x-rpd::icon name="trash-alt" click="$dispatch('deleteVariant',{variant: '{{$variant->id}}'})" confirm="delete variant {{ $variant->name }}?"  />
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif



    <x-rpd::modal
        name="editVariant"
        title="Edit Variant"
        action="save"
    >
        <div>
            <x-rpd::input inline model="variant.name" label="Name" />
            <x-rpd::input inline model="variant.sku" label="SKU" />

            <x-rpd::metadata
                model="metadata"
            />

        </div>
    </x-rpd::modal>


</div>
