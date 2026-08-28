<?php

namespace App\Modules\Shop\Listeners;



use App\Modules\Shop\Models\OrderItemAssignment;

class OrderItemAssignmentWorkflowSubscriber
{

    public function onGuardAssign($event)
    {
        /** @var OrderItemAssignment $assignment */
        $assignment = $event->getSubject();
        $order = optional($assignment->orderItem)->order;

        if($assignment->deliverable_type == 'inventory_item' && $order && !$order->workflow_metadata('final', $order->status)) {
            if(!$assignment->deliverable_id) {
                $event->setBlocked(true,'assign device');
            }
        } else {
            $event->setBlocked(true,'');
        }

    }

    public function onGuardGenerate($event)
    {
        /** @var OrderItemAssignment $assignment */
        $assignment = $event->getSubject();
        $order = optional($assignment->orderItem)->order;

        if($assignment->deliverable_type == 'service_item' && $order && !$order->workflow_metadata('final', $order->status)) {
            if($assignment->deliverable_id) {
                $event->setBlocked(true,'');
            }
        } else {
            $event->setBlocked(true,'');
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
            'workflow.order_item_assignment.guard.assign',
            'App\Modules\Shop\Listeners\OrderItemAssignmentWorkflowSubscriber@onGuardAssign'
        );
        $events->listen(
            'workflow.order_item_assignment.guard.generate',
            'App\Modules\Shop\Listeners\OrderItemAssignmentWorkflowSubscriber@onGuardGenerate'
        );

    }
}
