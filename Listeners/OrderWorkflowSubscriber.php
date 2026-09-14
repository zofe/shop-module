<?php

namespace App\Modules\Shop\Listeners;



use App\Modules\Shop\Models\Order;

class OrderWorkflowSubscriber
{

    public function onGuardPayOrder($event)
    {
        if (config('shop.checkout_mode', 'immediate') !== 'after_assignment') {
            return;
        }

        /** @var Order $order */
        $order = $event->getSubject();

        $block_count      = $order->workflow_count_transition_blocked_from($order->assignments);
        $incomplete_count = $order->workflow_count_incomplete_from($order->assignments);

        if ($block_count > 0 || $incomplete_count > 0) {
            $event->setBlocked(true, 'All delivery items must be assigned before payment.');
        }
    }


    /** pay_order: the order is due, a local payment record is opened (pending until confirmed). */
    public function onPayOrder($event)
    {
        /** @var Order $order */
        $order = $event->getSubject();
        app(\App\Modules\Shop\Payments\Contracts\PaymentRecorder::class)->pending($order->fresh());
    }

    /** payment_done reached by an operator: the pending payment record is confirmed by hand. */
    public function onPaymentDone($event)
    {
        /** @var Order $order */
        $order = $event->getSubject();
        $recorder = app(\App\Modules\Shop\Payments\Contracts\PaymentRecorder::class);
        if ($payment = $recorder->findPending($order->fresh())) {
            $recorder->confirm($payment, 'manual');
        }
    }

    public function onGuardCompleteOrder($event)
    {
        /** @var Order $order */
        $order = $event->getSubject();

        $incomplete_count = $order->workflow_count_incomplete_from($order->assignments);

        if ($incomplete_count > 0) {
            $event->setBlocked(true, 'please check delivery items');
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        // workflow.[workflow name].guard.[transition name] (block)
        // workflow.[workflow name].leave.[place name]
        // workflow.[workflow name].transition.[transition name]
        // workflow.[workflow name].enter.[place name]
        // workflow.[workflow name].completed.[transition name]

        $events->listen(
            'workflow.order.completed.pay_order',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onPayOrder'
        );
        $events->listen(
            'workflow.order.completed.payment_done',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onPaymentDone'
        );
        $events->listen(
            'workflow.order.guard.complete_order',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onGuardCompleteOrder'
        );
        $events->listen(
            'workflow.order.guard.pay_order',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onGuardPayOrder'
        );

    }
}
