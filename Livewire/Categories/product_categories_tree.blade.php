<x-rpd::card>
    <x-rpd::view title="Product Categories">

        <x-slot name="buttons">
            <x-rpd::icon name="plus-square" color="gray-600" click="$dispatch('addCategory')" />
{{--            <x-rpd::dropdown label="Add Category" color="outline-primary" action="addCategory" >--}}
{{--                <x-rpd::input col="col-md-12" wire:model="newcategory.name" label="Name" />--}}
{{--            </x-rpd::dropdown>--}}
        </x-slot>

        <x-rpd::modal
            name="addCategory"
            title="Add Category"
            action="save"
        >
            <div wire:key="add_in_{{$newcategory->parent_id}}">
                @if($newcategory->parent_id)
                    <h5>as subcategory of {{ $newcategory->parent_id }}</h5>
                @endif
                <x-rpd::input col="col-md-12" wire:model="newcategory.name" label="Name" />
            </div>
        </x-rpd::modal>


        <ul class="list-group mb-1" id="categories" wire:key="categories-{{ $refreshCounter }}">
            @foreach ($categories as $category)
                <li class="list-group-item mb-3" data-id="{{ $category->id }}">

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="h5"><i class="fa-solid fa-grip-vertical"></i> {{ $category->name }}</div>

                        <div class="btn-group">
                            <x-rpd::icon name="plus-square" color="text-secondary" click="$dispatch('addCategory',{parentId: {{$category->id}}})" />
                            <x-rpd::icon name="trash-alt" color="text-secondary" click="$dispatch('removeCategory',{categoryId: {{$category->id}}})" confirm="delete category {{ $category->name }}?"  />
                        </div>

                    </div>

                    @include('shop::Categories.product_categories_tree_nested', ['category' => $category])


                </li>
            @endforeach
        </ul>


    </x-rpd::view>
</x-rpd::card>

@once
    @push('footer_scripts')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
        <script type="application/javascript">

            var sortableInstances = [];
            function initializeSortables() {

                sortableInstances.forEach(function(instance) {
                    instance.destroy();
                });
                sortableInstances = [];

                var nestedLists = document.querySelectorAll('#categories, #categories ul');
                nestedLists.forEach(function(list) {
                    var instance = new Sortable(list, {
                        group: 'categories',
                        animation: 150,
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        onEnd: function (evt) {
                            var itemId = evt.item.dataset.id;
                            var newParentId = evt.to.getAttribute('data-parent-id') || null;
                            Livewire.dispatch('reorder', {itemId: itemId, newIndex: evt.newIndex, newParentId: newParentId});
                        }
                    });

                    sortableInstances.push(instance);
                });
            }

            document.addEventListener('livewire:init', function(){
                initializeSortables();
                Livewire.hook('morph.updated', (message, component) => {
                    initializeSortables();
                });
            });


        </script>
    @endpush
@endonce
