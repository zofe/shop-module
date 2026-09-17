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
php artisan db:seed --class="App\Modules\Shop\Database\Seeders\ShopSeeder"   # the demo catalogue (an IT shop), stock, three orders
```

The seeder attaches the demo orders to the first user of the application: a laptop (a physical product with serial
numbers), an extended warranty (a service, provisioned at payment) and both together. The plans (remote assistance,
antivirus, a bundle) are subscribed from the storefront.

## Documentation

| | |
|---|---|
| [docs/CATALOGUE.md](docs/CATALOGUE.md) | products, variants, bundles, price lists, metadata |
| [docs/ORDERS.md](docs/ORDERS.md) | the cart, the order workflow, delivery (assign, ship, complete), the customer's pages, permissions |
| [docs/SUBSCRIPTIONS.md](docs/SUBSCRIPTIONS.md) | the subscription flow, billing, lines, the services of a subscription |
| [docs/PROVISIONING.md](docs/PROVISIONING.md) | provisioning drivers, the `service_item` workflow, activation policies, licences, stock |
| [docs/PAYMENTS.md](docs/PAYMENTS.md) | payment methods, `zofe/payments-module`, taxes (flat, EU VAT + VIES, gateway tax) |

## In short

- **Catalogue**: products (physical, service, bundle) with variants and categories; price lists per role or per company
  say how each product is sold: once, or as a monthly / yearly fee with activation and trial.
- **Orders**: the cart → an order with a workflow (`pay_order` … `ship_order`, `complete_order`), one delivery
  assignment per unit: a serial number from the stock, or a service item with a licence.
- **Subscriptions**: a separate flow, a pending payment per period, services provisioned, suspended and terminated
  with the subscription.
- **Provisioning**: a `Provisioner` driver per product, chosen in the product form, called by the `service_item`
  workflow; activation automatic, manual or by the customer with a licence key.
- **Payments**: a `PaymentMethod` contract with a manual method built in; `zofe/payments-module` adds Stripe, Paddle,
  GoCardless. Taxes estimated from the billing address (EU VAT with VIES), final from the gateway.

## Tests

```bash
composer install && vendor/bin/phpunit
```
