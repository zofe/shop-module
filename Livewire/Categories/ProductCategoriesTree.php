<?php

namespace App\Modules\Shop\Livewire\Categories;

use Zofe\Rapyd\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\ProductCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class ProductCategoriesTree extends Component
{
    use WithDataTable, Authorize;

    public $slug;
    public $newcategory;

    public $categories;
    public $refreshCounter = 0;


    protected $rules = [
        'newcategory.parent_id' => 'nullable',
        'newcategory.name' => 'required|unique:product_categories,name',
    ];

    public function mount($slug=null)
    {
        $this->newcategory = new ProductCategory();
        $this->refreshCategories();
    }

    public function refreshCategories()
    {
        $this->categories = ProductCategory::whereNull('parent_id')->orderBy('order')->get();
    }

    public function booted()
    {
        $this->authorize('admin|view categories');
    }

    #[On('addCategory')]
    public function addCategory($parentId = null)
    {
        $this->newcategory->parent_id = $parentId;
        $this->dispatch('show-modal',['addCategory']);
    }

    public function save()
    {
        $this->validate();

        $this->newcategory->slug = Str::slug($this->newcategory->name);
        $this->newcategory->save();

        ProductCategory::reorderItem($this->newcategory->id, 0, $this->newcategory->parent_id);

        $this->newcategory = new ProductCategory();
        $this->refreshCategories();
        $this->dispatch('hide-modals');
    }

    #[On('removeCategory')]
    public function removeCategory($categoryId)
    {
        ProductCategory::destroy($categoryId);
        $this->refreshCategories();
    }


    #[On('reorder')]
    public function reorder($itemId, $newIndex, $newParentId = null)
    {
        ProductCategory::reorderItem($itemId, $newIndex, $newParentId);

        $this->refreshCategories();
        $this->refreshCounter++;
    }


    public function render()
    {
        return view('shop::categories.product_categories_tree')->layout('shop::admin');
    }
}
