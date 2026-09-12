<?php

namespace App\Modules\Shop\Services;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Shop\Cart\CartItem;
use App\Modules\Shop\CartFacade as Cart;
use App\Modules\Shop\Models\License;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItem;
use App\Modules\Shop\Models\OrderItemAssignment;
use App\Modules\Shop\Models\PriceList;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public static function createOrderFromCart($note = null, $user_id = null, $company_id = null)
    {
        if($user_id) {
            $user = User::find($user_id);
            $company = $user?->company;
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



            //$deliverableType = config("shop.deliverable_types.{$item->model->product->type}");
            $deliverableType = $item->model->product->type;
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'price_list_item_id' => $item->id,
                'deliverable_type' => $deliverableType,
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

            static::syncAssignments($orderItem);
        }

        if ($recalculate) {
            $order->recalculate();
            $order->refresh();
        }

    }



    public static function syncAssignments(OrderItem $orderItem): void
    {
        // Numero desiderato di unità
        $desiredCount = (int) $orderItem->qty;

        // Count attuale di assignments esistenti
        $existing = $orderItem->assignments()->count();

        DB::transaction(function () use ($orderItem, $desiredCount, $existing) {
            // Aggiungi assignments mancanti

            $subtotal = $orderItem->price;
            $tax = round($subtotal * ($orderItem->taxRate / 100), 2);
            $total =  round($subtotal + $tax, 2);



            if ($desiredCount > $existing) {
                $toAdd = $desiredCount - $existing;
                for ($i = 0; $i < $toAdd; $i++) {
                    $assignment = new OrderItemAssignment([
                        'order_item_id'     => $orderItem->id,
                        'deliverable_type'  => $orderItem->deliverable_type,
                        'deliverable_id'    => null,
                        'license_id'        => null,
                        'serial_number'     => null,
                        'metadata'          => null,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $total,
                        'status'            => 'pending',
                    ]);
                    $assignment->save();

                    // Se è un servizio, genera la licenza e associa
                    if ($orderItem->deliverable_type === \App\Models\ServiceItem::class) {
                        $license = License::create([
                            'service_item_id' => $orderItem->deliverable_id,
                            'order_id'        => $orderItem->order_id,
                            'order_item_id'   => $orderItem->id,
                            'status'          => 'active',
                            'activated_at'    => now(),
                            'expires_at'      => now()->addYear(),
                        ]);
                        $assignment->license_id = $license->id;
                        $assignment->save();
                    }
                }
            }

            // Rimuovi assignments in eccesso
            if ($existing > $desiredCount) {
                $toRemove = $existing - $desiredCount;
                $assignments = $orderItem->assignments()
                    ->oldest('id')
                    ->take($toRemove)
                    ->get();

                foreach ($assignments as $assignment) {
                    // Se c'è una licenza collegata, cancellala
                    if ($assignment->license_id) {
                        License::where('id', $assignment->license_id)->delete();
                    }
                    $assignment->delete();
                }
            }
        });
    }
}

