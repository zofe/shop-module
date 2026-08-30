<?php

namespace App\Modules\Shop\Listeners;



use App\Modules\Shop\Models\Order;

class OrderWorkflowSubscriber
{

    public function onGuardPayOrder($event)
    {
        /** @var Order $order */
        $order = $event->getSubject();

        // check if there are any assignments that are blocked
        $block_count = $order->workflow_count_transition_blocked_from($order->assignments);
        $incomplete_count = $order->workflow_count_incomplete_from($order->assignments);

        if($block_count>0 || $incomplete_count>0) {
            $event->setBlocked(true, 'please check delivery items ');
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
            'workflow.order.guard.complete_order',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onGuardCompleteOrder'
        );
        $events->listen(
            'workflow.order.guard.pay_order',
            'App\Modules\Shop\Listeners\OrderWorkflowSubscriber@onGuardPayOrder'
        );

    }
}
