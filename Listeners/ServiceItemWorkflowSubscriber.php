<?php

namespace App\Modules\Shop\Listeners;

use App\Modules\Shop\Models\ServiceItem;
use App\Modules\Shop\Provisioning\Provisioners;

/**
 * The `service_item` workflow drives the provisioning driver: each transition calls the
 * matching method of the product's Provisioner while the transition runs, so an exception
 * leaves the service in its previous state.
 */
class ServiceItemWorkflowSubscriber
{
    public function onProvision($event)
    {
        $this->driver($event->getSubject())->provision($event->getSubject());
    }

    public function onSuspend($event)
    {
        $this->driver($event->getSubject())->suspend($event->getSubject());
    }

    public function onResume($event)
    {
        $this->driver($event->getSubject())->resume($event->getSubject());
    }

    public function onTerminate($event)
    {
        $this->driver($event->getSubject())->terminate($event->getSubject());
    }

    protected function driver(ServiceItem $service)
    {
        return app(Provisioners::class)->for($service);
    }

    public function subscribe($events)
    {
        foreach (['provision', 'suspend', 'resume', 'terminate'] as $transition) {
            $events->listen('workflow.service_item.transition.' . $transition, self::class . '@on' . ucfirst($transition));
        }
    }
}
