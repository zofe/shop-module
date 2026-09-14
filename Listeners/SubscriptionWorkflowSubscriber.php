<?php

namespace App\Modules\Shop\Listeners;

use App\Modules\Shop\Models\Subscription;
use App\Modules\Shop\Services\SubscriptionService;

/** The subscription state machine driven by hand (an operator on the subscription page). */
class SubscriptionWorkflowSubscriber
{
    /** "activate" pressed: the period is considered paid (no payments module, or paid outside), the schedule moves on. */
    public function onActivate($event)
    {
        /** @var Subscription $subscription */
        $subscription = $event->getSubject();
        if (! $subscription->next_billing_at || $subscription->next_billing_at->lte(now())) {
            SubscriptionService::paymentConfirmed($subscription);
        }
    }

    public function onCancel($event)
    {
        /** @var Subscription $subscription */
        $subscription = $event->getSubject();
        if (! $subscription->ends_at) {
            $subscription->ends_at = now()->toDateString();
        }
    }

    public function subscribe($events)
    {
        $events->listen('workflow.subscription.completed.activate', self::class . '@onActivate');
        $events->listen('workflow.subscription.completed.cancel', self::class . '@onCancel');
    }
}
