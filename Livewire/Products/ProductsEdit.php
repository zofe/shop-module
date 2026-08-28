<?php

namespace App\Modules\Shop\Livewire\Products;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductsEdit extends Component
{
    use Authorize, WithFileUploads;

    public $product;
    public $availableCategories = [];
    public $types = [];
    public $image;

    protected function rules(): array
    {
        $uniqueName = $this->product->exists
            ? 'required|unique:products,name,' . $this->product->id
            : 'required|unique:products,name';

        $uniqueSku = $this->product->exists
            ? 'required|unique:products,sku,' . $this->product->id
            : 'required|unique:products,sku';

        return [
            'product.type'        => 'required',
            'product.name'        => $uniqueName,
            'product.description' => 'nullable',
            'product.sku'         => $uniqueSku,
            'product.category_id' => 'required',
            'image'               => 'nullable|image|max:2048',
        ];
    }

    public function booted(): void
    {
        $this->authorize('admin|edit products');
    }

    public function mount(?Product $product): void
    {
        $this->product = $product;
        $this->availableCategories = ProductCategory::getNestedDropdown();
        $this->types = config('shop.deliverable_types');
    }

    public function save(): mixed
    {
        $this->validate();

        $this->product->slug = Str::slug($this->product->name);

        if ($this->image) {
            $this->deleteImages($this->product->image_path);
            $this->product->image_path = $this->storeWithThumb($this->image);
        }

        $this->product->save();

        return redirect()->to(route_lang('products.table'));
    }

    public function removeImage(): void
    {
        if ($this->product->image_path) {
            $this->deleteImages($this->product->image_path);
            $this->product->image_path = null;
            $this->product->save();
        }
        $this->image = null;
    }

    private function storeWithThumb($file): string
    {
        $filename  = Str::uuid() . '.jpg';
        $thumbName = Str::beforeLast($filename, '.jpg') . '_thumb.jpg';
        $dir       = 'products';

        $base = Storage::disk('public')->path($dir);

        // Originale — max 1200px larghezza
        Image::decode($file->getRealPath())
            ->scaleDown(width: 1200)
            ->save("{$base}/{$filename}");

        // Thumb — 400×300, ritaglio centrato
        Image::decode($file->getRealPath())
            ->cover(400, 300)
            ->save("{$base}/{$thumbName}");

        return "{$dir}/{$filename}";
    }

    private function deleteImages(?string $imagePath): void
    {
        if (!$imagePath) return;

        Storage::disk('public')->delete($imagePath);

        $thumbPath = Str::beforeLast($imagePath, '.jpg') . '_thumb.jpg';
        Storage::disk('public')->delete($thumbPath);
    }

    public function render()
    {
        return view('shop::products.products_edit')->layout('shop::admin');
    }
}
