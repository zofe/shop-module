# Subscriptions

## The flow

A separate flow, no cart: "Subscribe monthly / yearly" on a product sold as a fee creates the **Subscription** with its
**SubscriptionItems** (the fees; a bundle unfolds its components) and the **first pending payment** (fee plus
activation), or a trial that is billed at its end. The customer's page (`/shop-subscription/{id}`) shows the lines, the
payment due with the payment methods and the payment history: the lines are changed by the operator only (the admin
page), where "mark paid", "failed", "bill now", "Add item" and the workflow transitions (`activate`, `past_due`,
`cancel`) live. The billing data come from the customer's company and addresses, nothing is chosen per subscription.

Every period `php artisan shop:bill-subscriptions` (schedule it daily) creates the next pending payment; a confirmed
payment extends `next_billing_at`, a pending one older than `SHOP_SUBSCRIPTION_GRACE_DAYS` (7) marks the subscription
past due. Payment records (`zofe/payments-module`) carry `subscription_id` for the first period and
`ref_subscription_id` for the following ones; invoices belong to an invoice module through `invoice_id`. Without a
payments module the operator drives the state machine by hand.

## The subscription workflow

State machine `subscription` (alias `subscription`): `pending`, `trialing`, `active`, `past_due`, `cancelled`.

| transition | from | what happens |
|---|---|---|
| `activate` | pending, trialing, past_due | the period is considered paid: `next_billing_at` moves on, the services are provisioned / resumed |
| `past_due` | active | the services are suspended by their driver |
| `cancel` | pending, trialing, active, past_due | `ends_at` is set, the services are terminated |

`SubscriptionService` is the API: `subscribe`, `addItem`, `updateItem` (quantity / variant at the current price),
`removeItem`, `billPeriod`, `billDue`, `paymentConfirmed`, `paymentFailed`, `markPastDue`, `cancel`.

## Lines

Each line (`subscription_items`) keeps `price_list_item_id`, `product_variant_id`, `period`, `qty` and the fee; the
components of a bundle carry `bundle_code` and cost 0. The admin page has "Add item" (a product sold as a fee for the
period, its variant, the quantity) and an edit icon per line (quantity, variant): prices come from the subscription's
price list as it is now. Each service line points to its `ServiceItem` (`deliverable`) once provisioned.

## Service items of a subscription

When a period is paid (or a trial starts) every service line gets its `ServiceItem` (owner: the customer; origin: the
subscription item; parameters: the price list row) and a licence that lasts until `next_billing_at`; a renewal extends
the licence, `past_due` suspends the service, `cancel` terminates it. See [PROVISIONING.md](PROVISIONING.md).
