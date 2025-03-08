<ul class="list-group py-1" data-parent-id="{{ $category->id }}">


@foreach ($category->children as $subcategory)
        <li class="list-group-item mb-2" data-id="{{ $subcategory->id }}">

            <div class="d-flex justify-content-between align-items-center">
                <div class="h6"><i class="fa-solid fa-grip-vertical text-gray-400"></i> {{ $subcategory->name }}</div>

                <div class="btn-group">
                    <x-rpd::icon name="plus-square" color="gray-600" click="$dispatch('addCategory',{parentId: {{$subcategory->id}}})" />
                    <x-rpd::icon name="trash-alt" color="gray-600" click="$dispatch('removeCategory',{categoryId: {{$subcategory->id}}})" confirm="delete category {{ $subcategory->name }}?"  />
                </div>

            </div>


            @include('shop::Categories.product_categories_tree_nested', ['category' => $subcategory])


        </li>
    @endforeach
</ul>



