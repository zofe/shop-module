# Provisioning

## What happens to a sold service

What happens to the goods once they are sold. Everything goes through the workflows (`workflow.php`), so an
application can change guards, listeners and transitions.

**Services** (`service_item` products, and the service components of a bundle). A paid order, or an active
subscription, gets one `ServiceItem` per unit with its owner (the company, else the user), its origin (the order
assignment or the subscription item) and a `License`. The item's status is the `service_item` workflow
(`new → active → suspended / terminated`) and every transition calls the product's **provisioning driver**:

```php
// config/shop.php
'provisioning' => [
    'drivers' => ['myapp' => App\Provisioning\MyAppProvisioner::class],
    'auto_on_payment'  => true,        // services of an order are generated (record + licence) at payment_done
    'require_shipping' => true,        // physical goods: the order completes only once shipped
    'license_months'   => 12,          // licence of a service sold by an order
    'activation'       => 'automatic', // automatic | manual | customer: who activates a generated service (see below)
],
```

```php
class MyAppProvisioner implements App\Modules\Shop\Provisioning\Contracts\Provisioner
{
    public function provision(ServiceItem $service): void
    {
        $account = MyApp::createAccount($service->owner, $service->product);   // throw to abort the transition
        $service->external_ref = $account->id;                                 // saved with the item
    }
    public function suspend(ServiceItem $service): void { MyApp::suspend($service->external_ref); }
    public function resume(ServiceItem $service): void { MyApp::resume($service->external_ref); }
    public function terminate(ServiceItem $service): void { MyApp::delete($service->external_ref); }
}
```

The driver is chosen per product ("Provisioning driver" in the product form, `products.provisioner`); a product
that names none gets the default driver, which only records the steps: the item and its licence are the
provisioning (a support plan, a manual activation…).

- Order: at `payment_done` the services are generated (`generate` on the assignment) and licensed for
  `license_months`; then activated according to the activation policy. Set `auto_on_payment` to false to leave the
  "generate" button to the operator.
- Subscription: when a period is paid (or the trial starts) the services are provisioned, suspended ones resume and
  the licences are extended to the next billing date; `past_due` suspends them, `cancel` terminates them.
- `ProvisioningService::transition($service, 'suspend' | 'resume' | 'terminate')` from your own code, or the
  buttons of the service item page (Provisioning → Services).

**Physical goods** (`inventory_item` products). Each unit of an order is an assignment the operator fills with a
serial number from the stock (`assign`). Then the order is shipped (`ship_order`: carrier and tracking code) and
completed: the shipped items get the customer as owner and the status `sold`. An order with physical goods cannot
be completed before every unit is assigned and, unless `require_shipping` is false, shipped. Orders of services only
skip the shipping step.

## Activation policies

A generated service is **activated** by the `provision` transition, which calls the driver. Who triggers it is the
activation policy, `config('shop.provisioning.activation')` (env `SHOP_ACTIVATION`) or, per product, the "Activation"
field of the product form (`products.activation`):

| policy | when `provision` runs | typical case |
|---|---|---|
| `automatic` (default) | as soon as the service is generated (at payment) | a service the buyer uses right away: support, setup, a plan |
| `manual` | the operator presses "provision" on the service item, or your code calls `ProvisioningService::activate($service)` | an activation that needs a check or a manual step |
| `customer` | the end user redeems the **licence key** (`licenses.key`, `XXXX-XXXX-XXXX-XXXX`): `ProvisioningService::redeemable($key)` then `activate($service, $endUser)` | B2B: sold to a reseller, activated by the final customer, who becomes the owner of service and licence |

Until then the service item stays `new` and its licence `inactive` (no activation date). Subscriptions always activate
at payment: the fee is the service in use.

## Data

| table | what |
|---|---|
| `service_items` | one per sold unit: `product_id`, `owner` (user or company), `origin` (`order_item_assignment` or `subscription_item`), `provisioner`, `external_ref` (the driver's reference), `metadata` (parameters + what the driver notes), `status` (the `service_item` workflow) |
| `licenses` | one per service item: `key`, `status` (`inactive` / `active`), `activation_date`, `expire_date` (12 months for an order, the next billing date for a subscription), `owner` |
| `inventory_items` | the stock: `serial_number`, `status` (`in_stock`, `assigned`, `sold`), `owner` once sold |

The service item page (Provisioning → Services) shows all of it with the workflow buttons (`provision`, `suspend`,
`resume`, `terminate`); every step is a `WorkflowStep` in the history.
