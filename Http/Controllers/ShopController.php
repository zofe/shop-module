<?php


namespace App\Modules\Shop\Http\Controllers;

use Illuminate\Routing\Controller;
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

    /** A document for a subject: the owner of the subject, or the back office. */
    public function document(string $type, string $id, string $document)
    {
        $model = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($type);
        abort_unless($model && in_array($type, ['order', 'subscription', 'service_item'], true), 404);
        $subject = $model::findOrFail($id);
        $user = auth()->user();
        $owns = ($subject->user_id ?? null) === $user->id
            || (($subject->owner_type ?? null) === 'user' && ($subject->owner_id ?? null) === $user->id)
            || (($subject->company_id ?? $subject->owner_id ?? null) && ($user->company_id ?? null) && ($subject->company_id ?? $subject->owner_id) === $user->company_id);
        abort_unless($owns || $user->hasRoleOrPermission('admin|view orders|view subscriptions|view service items'), 403);

        $documents = app(\App\Modules\Shop\Documents\Documents::class);
        abort_unless(isset($documents->available($subject)[$document]), 404);

        return $documents->renderer()->render($document, $subject);
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
