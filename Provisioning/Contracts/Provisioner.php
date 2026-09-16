<?php

namespace App\Modules\Shop\Provisioning\Contracts;

use App\Modules\Shop\Models\ServiceItem;

/**
 * What makes a sold service real: an account, an instance, an API key, a licence file…
 *
 * A driver is registered in config('shop.provisioning.drivers') under a name and chosen per
 * product (products.provisioner); the ServiceItem carries the owner (user or company), the
 * origin (an order assignment or a subscription item), the licence and a metadata array the
 * driver may fill (external_ref for its own reference). Each method runs inside the
 * corresponding transition of the `service_item` workflow: throw to abort it.
 */
interface Provisioner
{
    /** new → active: the service starts working for its owner. */
    public function provision(ServiceItem $service): void;

    /** active → suspended: unpaid renewal, past due subscription… keep the data, stop the service. */
    public function suspend(ServiceItem $service): void;

    /** suspended → active: the payment arrived. */
    public function resume(ServiceItem $service): void;

    /** → terminated: cancelled or expired; the driver may delete or archive. */
    public function terminate(ServiceItem $service): void;
}
