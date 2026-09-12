<?php

namespace App\Modules\Shop\Listeners;

use App\Modules\Payments\Events\PaymentConfirmed;
use App\Modules\Shop\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentConfirmedListener
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        if (! $payment->order_id) {
            return;
        }

        $order = Order::find($payment->order_id);

        if (! $order) {
            return;
        }

        try {
            $workflow = \Workflow::get($order, 'order');

            if ($workflow->can($order, 'check_payment')) {
                $workflow->apply($order, 'check_payment');
            }

            if ($workflow->can($order, 'payment_done')) {
                $workflow->apply($order, 'payment_done');
            }

            $order->save();

            Log::info('Order advanced after payment confirmed', [
                'order_id'   => $order->id,
                'status'     => $order->status,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentConfirmedListener: workflow transition failed', [
                'order_id'   => $order->id,
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
