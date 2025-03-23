<?php

namespace App\Modules\Shop\Services;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Shop\Cart\CartItem;
use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\License;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItem;
use App\Modules\Shop\Models\PriceList;

class OrderService
{
    public static function createOrderFromCart($note = null, $user_id = null, $company_id = null)
    {
        if($user_id) {
            $user = User::find($user_id);
            $company = optional($user->companies())->first();
        } elseif ($company_id) {
            $company = Company::find($company_id);
            $user = $company->owner;
        }


        if (! $user && ! $company) {
            throw new \InvalidArgumentException('createOrderFromCart: Please supply a valid user or company');
        }

        $order = new Order();
        $order->id = (string) Cart::uuid();
        $order->user_id = $user->id;
        $order->company_id = optional($company)->id;

        $order->price_list_id = optional($company)->pricelist_id ?? PriceList::where('is_default', 1)->first()->id;

        $order->discount = Cart::discountFloat();
        $order->subtotal = Cart::subtotalFloat();

        $taxRate = ($company) ? (int) $company->tax_perc : config('shop.tax');
        $tax = (Cart::subtotalFloat() + Cart::shippingFloat()) * $taxRate / 100;
        $total = round(Cart::subtotalFloat() + Cart::shippingFloat() + $tax, 2);

        $order->tax = round($tax, 2);
        $order->shipping = Cart::shippingFloat();
        $order->total = $total;
        $order->note = $note;

        $order->save();

        if (! $order) {
            return false;
        }

        self::addItemsFromCart('default', $order);

        return $order;
    }


    public static function addItemsFromCart($cartInstance, Order $order, $recalculate = false)
    {
        Cart::instance($cartInstance);

        /** @var CartItem $item */
        foreach (Cart::content() as $item) {
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'price_list_item_id' => $item->id,
                'prd_code' => $item->getSku(),
                'name' => $item->name,
                'qty' => $item->qty,
                'price' => $item->price, //+ $item->priceActivation,
                'subtotal' => $item->subtotal,
                'discountRate' => $item->discountRate,
                'taxRate' => $item->taxRate,
                'shipping' => $item->shipping,
                'bundle_code' => isset($item->options['bundle_code']) ? $item->options['bundle_code'] : 0,
            ]);
            $orderItem->save();


//            if ($item->options && isset($item->options['item_type']) && $item->options['item_type'] == 'license') {
//                $subtotal = $orderItem->price;
//                $tax = round($subtotal * ($item->taxRate / 100), 2);
//
//                for ($i = 1; $i <= $item->qty; $i++) {
//                    $license = new License();
//                    $license->duration = $item->options['duration'];
//                    $license->license_type_id = $item->options['license_type_id'];
//
//
//                    $license->order_id = $order->id;
//                    $license->order_item_id = $orderItem->id;
//                    $license->company_id = $order->company_id;
//                    $license->commercial_id = optional($order->company)->commercial_id;
//                    $license->subtotal = $subtotal;
//                    $license->tax = $tax;
//                    $license->total = round($subtotal + $tax, 2);
//                    $license->order_date = $order->created_at;
//                    $license->save();
//                }
//            } elseif ($item->options && isset($item->options['item_type']) && in_array($item->options['item_type'], $servicesList)) {
//                $subtotal = $orderItem->price;
//                $tax = round($subtotal * ($item->taxRate / 100), 2);
//
//                for ($i = 1; $i <= $item->qty; $i++) {
//                    $license = new ServiceLicense();
//                    $license->service_name = $item->options['item_type'];
//                    $license->service_type_id = @ServiceType::whereSlug($item->options['item_type'])->first()->id;
//                    $license->duration = $item->options['duration'];
//                    $license->box_type_id = isset($item->options['box_type_id']) ? $item->options['box_type_id'] : null;
//                    $license->is_renew = isset($item->options['isRenew']) ? 1 : 0;
//                    // $license->is_shield_starter = isset($item->options['isSHIELDSTARTER']) ? 1 : 0;
//                    // $license->is_dr_starter = isset($item->options['isDRSTARTER']) ? 1 : 0;
//                    $license->is_starter = isset($item->options['isSTARTER']) ? 1 : 0;
//                    $license->is_shield_starter = isset($item->options['isSHIELDSTARTER']) ? 1 : 0;
//                    $license->is_dr_starter = isset($item->options['isDRSTARTER']) ? 1 : 0;
//
//                    $license->order_id = $order->id;
//                    $license->order_item_id = $orderItem->id;
//                    $license->company_id = $order->company_id;
//                    $license->commercial_id = optional($order->company)->commercial_id;
//                    $license->subtotal = $subtotal;
//                    $license->tax = $tax;
//                    $license->total = round($subtotal + $tax, 2);
//                    $license->order_date = $order->created_at;
//                    $license->save();
//                }
//            } else {
//                //le routerboard e i box vanno assegnati (all'ordine e al partner)
//                $product = $orderItem->price_list_item->product;
//                if (in_array($product->model_type, ['App\Model\Box', 'App\Model\Routerboard'])) {
//                    for ($i = 1; $i <= $item->qty; $i++) {
//                        $orderDevice = new OrderDevice([
//                            'order_id' => $order->id,
//                            'order_item_id' => $orderItem->id,
//                            'serial_number' => null,
//                            'model_type' => $product->model_type,
//                            'model_type_id' => $product->model_type_id,
//                            'is_nfr' => $orderItem->is_nfr,
//                            'is_starter' => $orderItem->is_starter,
//                            'is_shield_starter' => $orderItem->is_shield_starter,
//                            'is_dr_starter' => $orderItem->is_dr_starter,
//                        ]);
//                        $orderDevice->save();
//                    }
//                }
//            }
        }

        if ($recalculate) {
            $order->recalculate();
            $order->refresh();
        }
    }
}
