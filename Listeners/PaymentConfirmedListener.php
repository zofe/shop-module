<?php

namespace App\Modules\Shop\Listeners;

use App\Modules\Payments\Events\PaymentConfirmed;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;

/** A payment record was confirmed (gateway webhook or operator): the order or the subscription moves on. */
class PaymentConfirmedListener
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        if ($payment->order_id && ($order = Order::find($payment->order_id))) {
            $this->orderPaid($order, $payment);
        }

        $subscriptionId = $payment->subscription_id ?: $payment->ref_subscription_id;
        if ($subscriptionId && ($subscription = Subscription::find($subscriptionId))) {
            SubscriptionService::paymentConfirmed($subscription, $payment);
            Log::info('Subscription extended after payment confirmed', ['subscription_id' => $subscription->id, 'payment_id' => $payment->id]);
        }
    }

    protected function orderPaid(Order $order, $payment): void
    {
        // What the gateway charged is what the customer paid: the tax is final now
        $meta = $payment->metadata ?? [];
        $source = ($meta['tax_source'] ?? 'estimate') === 'estimate' ? 'gateway:' . $payment->gateway : $meta['tax_source'];
        OrderService::applyPayment($order, [
            'tax' => $payment->tax, 'total' => $payment->total, 'subtotal' => $payment->subtotal, 'shipping' => $payment->shipping,
        ], $source);

        try {
            $workflow = \Workflow::get($order, 'order');
            if ($workflow->can($order, 'check_payment')) {
                $workflow->apply($order, 'check_payment');
            }
            if ($workflow->can($order, 'payment_done')) {
                $workflow->apply($order, 'payment_done');
            }
            $order->save();
            Log::info('Order advanced after payment confirmed', ['order_id' => $order->id, 'status' => $order->status, 'payment_id' => $payment->id]);
        } catch (\Exception $e) {
            Log::error('PaymentConfirmedListener: workflow transition failed', ['order_id' => $order->id, 'payment_id' => $payment->id, 'error' => $e->getMessage()]);
        }
    }
}
