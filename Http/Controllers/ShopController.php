<?php


namespace App\Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shop\Models\InventoryItem;
use App\Modules\Shop\Models\PriceListItem;


class ShopController extends Controller
{

    public function __construct()
    {
    }

    public function ajax_available_pricelist_items()
    {
        $prices = PriceListItem::whereHas('product', function($q)  {
            $q->where('name', 'like', request()->query('q').'%');
        })->get()->map(function ($price) {
            $item = new \stdClass();
            $item->id = $price->id;
            $item->title = '<div class="fw-bold">'.$price->product->name.'</div>';
            //$item->title = "<strong>Debug</strong>";//view($result->item_view, ['item' => $result])->render();
            return $item;
        });

        return response()->json($prices);
    }

    public function ajax_available_inventory_items()
    {
        $items = InventoryItem::with('product')
            ->where('status', 'in_stock')
            ->where('serial_number', 'like', request()->query('q') . '%')
            ->get()
            ->map(function ($item) {
                $obj = new \stdClass();
                $obj->id    = $item->id;
                $obj->title = '<div class="fw-bold">' . $item->serial_number . '</div>'
                            . '<div class="small text-muted">' . optional($item->product)->name . '</div>';
                return $obj;
            });

        return response()->json($items);
    }

    public function ajax_product_pricelist_items()
    {
        $prices = PriceListItem::whereHas('product', function($q)  {
            $q->where('name', 'like', request()->query('q').'%');
        })->get()->map(function ($price) {
            $item = new \stdClass();
            $item->id = $price->id;
            $item->title = '<div class="fw-bold">'.$price->product->name.'</div>';
            //$item->title = "<strong>Debug</strong>";//view($result->item_view, ['item' => $result])->render();
            return $item;
        });

        return response()->json($prices);
    }

}
