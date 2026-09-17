# Orders

## The cart and the order

One-time purchases: the customer adds products to the cart, picks a shipping address (physical goods) and makes the
order. `pay_order` opens a local **payment record** (pending) when `zofe/payments-module` is installed; the customer
pays it with one of the payment methods, the gateway's webhook or an operator confirms it, the order reaches
`payment_done` and delivery / licence generation follow in the order workflow. With `SHOP_CHECKOUT_MODE=after_assignment`
payment waits until an operator has assigned every delivery item.

## The order workflow

`workflow.php` of the module, state machine `order` (the model's morph alias is `order`):

```
new ──pay_order──▶ pending_payment ──check_payment──▶ payment_verification ──payment_done──▶ payment_done
                                                              │                                   │
                                                        payment_failed                      process_order
                                                                                                  ▼
                                    completed ◀──complete_order── shipped ◀──ship_order── in_process
                                        ▲                                                         │
                                        └────────────── complete_order (services only) ───────────┘
cancel_order: from new, pending_payment, payment_verification, payment_failed
```

| transition | who | what happens (listeners in `Listeners/OrderWorkflowSubscriber`) |
|---|---|---|
| `pay_order` | the customer ("Make Order") | a pending payment record is opened (with `zofe/payments-module`) |
| `check_payment`, `payment_done` | the gateway webhook, or the operator | the record is confirmed; **the services are generated** (see below) |
| `process_order` | the operator | |
| `ship_order` | the operator (modal: carrier, tracking code, delivery note) | only for orders with physical goods, once every unit is assigned |
| `complete_order` | the operator | every delivery item must be final; physical goods must be shipped unless `shop.provisioning.require_shipping` is false; **the shipped units become the customer's** (`inventory_items.owner`, status `sold`) |

## Delivery: one assignment per unit

Each order line has one `OrderItemAssignment` per unit (`syncAssignments`), with its own state machine
`order_item_assignment`: `pending → assigned` (physical: the operator picks a serial number from the stock, modal
"assign item") or `pending → generated` (service: a `ServiceItem` + `License` are created, automatically at
`payment_done` or by the "generate" button, never before the payment). The order page shows, per unit, the serial
number or the service item with its licence and state.

## The customer's pages

`/shop-orders` (own orders, with a summary) and `/shop-order/{id}` (detail, the payment methods while a payment is due,
delivery with serial numbers / licences, shipping data once shipped). Permissions: `view own orders`, `edit own orders`,
`pay own orders` (given to `customer`, `tier1`, `tier2` by the module config, see [Permissions](#permissions)).

## Permissions

`config.php` of the module declares the permissions of the shop and adds them to the roles of rapyd-admin
(`auth.permissions` / `auth.role_permissions`, created by the `AuthSeeder`):

| role | permissions |
|---|---|
| operator | `view/edit products`, `categories`, `prices`, `edit price lists`, `view/edit orders`, `subscriptions`, `inventory items`, `service items` |
| customer, tier1, tier2 | `view own orders`, `edit own orders`, `pay own orders` |

`admin` has everything. Run the seeder again after installing the module: `php artisan db:seed --class="Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder"`.
