# Shop module for rapyd-admin

<a href="https://github.com/zofe/shop-module/actions/workflows/run-tests.yml"><img src="https://github.com/zofe/shop-module/actions/workflows/run-tests.yml/badge.svg" alt="Tests"></a>

Products with variants and categories, price lists, a cart, orders with a delivery workflow (inventory items and
services assigned to each order line), subscriptions and licences. A module package of
[rapyd-admin](https://github.com/zofe/rapyd-admin): admin pages under `/products`, `/orders`, `/pricelists`,
`/subscriptions`, a storefront under `/shop`.

```bash
composer require zofe/shop-module
php artisan migrate
php artisan storage:link                                                    # product images live on the public disk
php artisan db:seed --class="App\Modules\Shop\Database\Seeders\ShopSeeder"   # two products with images, two orders
```

The seeder attaches the demo orders to the first user of the application.

## Price lists

A price list row says how a product (or one of its variants) is sold:

| Flag | Price | Meaning |
|---|---|---|
| `has_onetime_payment` | `price_onetime` | bought once, through the cart → an **order** |
| `fee_canbe_monthly` / `fee_canbe_yearly` | `fee_monthly` / `fee_yearly` | a recurring fee → a **subscription** |
| `has_activation_price` | `price_activation` | charged once with the first fee |
| | `trial_days` | the subscription starts free, the first fee is due at the end of the trial |

A product may carry both (a device bought once, a service on a fee). **Variants** (`product_variants`: a size, a plan
tier, a licence size) have their own SKU and stock and their own price list rows. A **bundle** (`products.type =
bundle`, `product_bundle_items`) is sold at its own price and unfolds into its components at 0, grouped by
`bundle_code`, so delivery and licences stay per component. Price lists per role (`price_lists.role`) fall back to the
default list; a company can have a list of its own (`companies.pricelist_id`).

## Orders (the cart)

One-time purchases: the customer adds products to the cart, picks a shipping address (physical goods) and makes the
order. `pay_order` opens a local **payment record** (pending) when `zofe/payments-module` is installed; the customer
pays it with one of the payment methods, the gateway's webhook or an operator confirms it, the order reaches
`payment_done` and delivery / licence generation follow in the order workflow. With `SHOP_CHECKOUT_MODE=after_assignment`
payment waits until an operator has assigned every delivery item.

## Subscriptions

A separate flow, no cart: "Subscribe monthly / yearly" on a product sold as a fee creates the **Subscription** with its
**SubscriptionItems** (the fees; a bundle unfolds its components) and the **first pending payment** (fee plus
activation), or a trial that is billed at its end. The customer's page (`/shop-subscription/{id}`) shows items, billing
address, the payment due with the payment methods, and lets them add or remove fees; the admin page adds "mark paid",
"failed", "bill now" and the workflow transitions (`activate`, `past_due`, `cancel`).

Every period `php artisan shop:bill-subscriptions` (schedule it daily) creates the next pending payment; a confirmed
payment extends `next_billing_at`, a pending one older than `SHOP_SUBSCRIPTION_GRACE_DAYS` (7) marks the subscription
past due. Payment records (`zofe/payments-module`) carry `subscription_id` for the first period and
`ref_subscription_id` for the following ones; invoices belong to an invoice module through `invoice_id`. Without a
payments module the operator drives the state machine by hand.

## Payment methods

The checkout offers the methods of `config('shop.payment_methods')`, classes implementing
`App\Modules\Shop\Payments\Contracts\PaymentMethod` on a `Payable` (an order or a subscription period): `label()`,
`available($payable)`, `start($payable, $pendingPayment)` returning a redirect URL or a message. The shop ships
**ManualPayment** (bank transfer, a link sent by hand…: the record stays pending until an operator confirms it).
`zofe/payments-module` adds its gateways when installed (labels in `config('shop.gateway_methods')`; Paddle is offered
for digital goods only); its `PaymentConfirmed` event confirms orders and subscriptions. Your own gateway: one class,
its name in `payment_methods`.

## Taxes

The cart and the order show a **tax estimate** computed from the customer's billing data: the VAT number of their
company and the country of their address (`country_code` is required on addresses since rapyd-admin 9.8). When a gateway
confirms the payment (`PaymentConfirmed` of `zofe/payments-module`), what it charged becomes the final tax and total
of the order: with Stripe Tax (`STRIPE_AUTOMATIC_TAX=true`) the tax Stripe computed from the billing address, otherwise
the estimate as it was. The order records rate, rule and source (`tax_rate`, `tax_reason`, `tax_source`, `tax_final`).

```dotenv
SHOP_TAX_RESOLVER=flat      # flat (default): SHOP tax rate for everybody
SHOP_TAX_RESOLVER=eu_vat    # the rules of a seller established in the EU
SHOP_SELLER_COUNTRY=IT
```

`eu_vat` applies: the seller's rate at home (`domestic`); 0% reverse charge for an EU business whose VAT number VIES
confirms (`eu_reverse_charge`, answers cached for a month); the customer's country rate for EU consumers and for
unconfirmed VAT numbers (`eu_b2c`); 0% outside the EU (`export`); the seller's rate, flagged `unknown_country`, when
the address has no country yet. Standard rates live in `App\Modules\Shop\Tax\EuRates`, `config('shop.tax_rates')`
overrides them. Your own rules: a class implementing `App\Modules\Shop\Tax\Contracts\TaxResolver`, its name in
`SHOP_TAX_RESOLVER`.

## Provisioning

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
    'auto_on_payment'  => true,   // services of an order are provisioned at payment_done
    'require_shipping' => true,   // physical goods: the order completes only once shipped
    'license_months'   => 12,     // licence of a service sold by an order
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

- Order: at `payment_done` the services are generated (`generate` on the assignment), licensed for
  `license_months` and provisioned. Set `auto_on_payment` to false to leave the "generate" button to the operator.
- Subscription: when a period is paid (or the trial starts) the services are provisioned, suspended ones resume and
  the licences are extended to the next billing date; `past_due` suspends them, `cancel` terminates them.
- `ProvisioningService::transition($service, 'suspend' | 'resume' | 'terminate')` from your own code, or the
  buttons of the service item page (Provisioning → Services).

**Physical goods** (`inventory_item` products). Each unit of an order is an assignment the operator fills with a
serial number from the stock (`assign`). Then the order is shipped (`ship_order`: carrier and tracking code) and
completed: the shipped items get the customer as owner and the status `sold`. An order with physical goods cannot
be completed before every unit is assigned and, unless `require_shipping` is false, shipped. Orders of services only
skip the shipping step.

## Tests

```bash
composer install && vendor/bin/phpunit
```
