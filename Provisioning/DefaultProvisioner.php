<?php

namespace App\Modules\Shop\Provisioning;

use App\Modules\Shop\Models\ServiceItem;
use App\Modules\Shop\Provisioning\Contracts\Provisioner;

/**
 * The driver used when a product names none: nothing to call, the service item and its
 * licence are the provisioning (a licence key, a support plan, a manual activation…).
 * It only notes when each step happened, so the record tells its story.
 */
class DefaultProvisioner implements Provisioner
{
    public function provision(ServiceItem $service): void
    {
        $this->note($service, 'provisioned_at');
    }

    public function suspend(ServiceItem $service): void
    {
        $this->note($service, 'suspended_at');
    }

    public function resume(ServiceItem $service): void
    {
        $this->note($service, 'resumed_at');
    }

    public function terminate(ServiceItem $service): void
    {
        $this->note($service, 'terminated_at');
    }

    protected function note(ServiceItem $service, string $key): void
    {
        $service->metadata = array_merge($service->metadata ?? [], [$key => now()->toDateTimeString()]);
    }
}
