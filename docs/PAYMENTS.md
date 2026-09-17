# Payment methods and taxes

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
