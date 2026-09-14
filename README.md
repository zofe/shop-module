# Shop module for rapyd-admin

<a href="https://github.com/zofe/shop-module/actions/workflows/run-tests.yml"><img src="https://github.com/zofe/shop-module/actions/workflows/run-tests.yml/badge.svg" alt="Tests"></a>

Products with variants and categories, price lists, a cart, orders with a delivery workflow (inventory items and
services assigned to each order line), subscriptions and licences. A module package of
[rapyd-admin](https://github.com/zofe/rapyd-admin): admin pages under `/products`, `/orders`, `/pricelists`,
`/subscriptions`, a storefront under `/shop`.

```bash
composer require zofe/shop-module
php artisan migrate
php artisan db:seed --class="App\Modules\Shop\Database\Seeders\ShopSeeder"   # two products, two orders
```

The seeder attaches the demo orders to the first user of the application.

## Checkout

A customer adds products to the cart, saves a shipping address (the Addresses module of rapyd-admin) and makes the
order. With `SHOP_CHECKOUT_MODE=immediate` (default) the order goes straight to `pending_payment`; with
`after_assignment` payment waits until an operator has assigned every delivery item.

## Payment methods

The checkout page offers the methods of `config('shop.payment_methods')`, classes implementing
`App\Modules\Shop\Payments\Contracts\PaymentMethod` (`label()`, `available($order)`, `start($order)` returning a
redirect URL or a message). The shop ships **ManualPayment**: the order moves to *payment verification*, the customer
reads the instructions of `config('shop.manual_payment')` (bank transfer, a PayPal link you send by hand…) and an
operator confirms with the *payment done* transition on the order page. No gateway, no subscription.

`zofe/payments-module` (Stripe, GoCardless, Paddle) adds its gateways automatically when installed, with the labels
of `config('shop.gateway_methods')`; the order is confirmed by its `PaymentConfirmed` event. Your own gateway: one
class, its name in `payment_methods`.

## Taxes

The cart and the order show a **tax estimate** computed from the customer's billing data: the VAT number of their
company and the country of their address (`country_code` is required on addresses since rapyd-admin 9.8). A payment
gateway able to compute taxes may replace the estimate with the final amount; the order records the rate, the rule
and the source (`tax_rate`, `tax_reason`, `tax_source`, `tax_final`).

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

## Tests

```bash
composer install && vendor/bin/phpunit
```
