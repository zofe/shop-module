# Changelog

## [1.0.0] - 2026-09-17

The shop as it was redesigned in September 2026. Breaking with 0.12: two flows (orders and subscriptions), price
lists per role with variants and bundles, provisioning.

- Catalogue: products (physical, service, bundle), variants with attributes, categories; price lists per role or
  company: one-time price, monthly / yearly fee, activation, trial; metadata on variants and price rows.
- Orders: cart → order workflow (`pay_order` … `ship_order`, `complete_order`), one delivery assignment per unit
  (serial number from the stock, or a service item with a licence), shipping with carrier, tracking and delivery note.
- Subscriptions: a separate flow, a pending payment record per period (`zofe/payments-module`), `shop:bill-subscriptions`,
  grace period, lines editable by the operator at the current prices.
- Provisioning: `Provisioner` drivers per product, `service_item` workflow, activation policies (automatic, manual,
  by the customer with a licence key), licences, stock attributed to the customer.
- Payments: `PaymentMethod` contract with a manual method; gateways from `zofe/payments-module`; taxes estimated
  from the billing address (EU VAT with VIES), final from the gateway (Stripe Tax).
- Documents: `DocumentRenderer` contract (a documents module produces PDFs / Excel).
- Permissions declared by the module; customer pages for orders and subscriptions.
- Demo seed: an IT shop (laptop, printer, on-site setup, extended warranty, remote assistance, antivirus, a bundle).
- Docs in `docs/`: CATALOGUE, ORDERS, SUBSCRIPTIONS, PROVISIONING, PAYMENTS, DOCUMENTS.
