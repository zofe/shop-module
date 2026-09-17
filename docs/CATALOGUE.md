# Catalogue and prices

Products, variants, bundles, categories and price lists: what the shop sells and how.

## Products

`products.type` says what a sold unit is:

| type | a unit is… | after the sale |
|---|---|---|
| `inventory_item` | a physical unit with a serial number (`inventory_items`) | assigned to the order by the operator, shipped, then owned by the customer |
| `service_item` | a service instance (`service_items`) with a licence | generated and provisioned when the order is paid, or when a subscription period is paid |
| `bundle` | a fee that unfolds into several services (`product_bundle_items`) | one service item per component |

A product belongs to a category (`product_categories`, a tree), has a SKU, a description and an image (public disk,
`products/`). Services choose a **provisioning driver** and an **activation policy** (see [PROVISIONING.md](PROVISIONING.md)).

## Variants

`product_variants` are the commercial variants of a product (a size, a configuration, what a warranty covers): their own
SKU and stock, **no price of their own**: the price list has one row per variant. `metadata` holds the attributes of the
variant (`ram: 16 GB`, `covers: laptop`…), shown on the product page and handed to the provisioning driver.

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

`metadata` on a price list row holds the **parameters of the service** sold at that price (`devices: 5`, `hours: 2`…):
they end up in the service item when it is provisioned, so a driver knows what to set up. The row form edits them.

An order line or a subscription line keeps `price_list_item_id` and `product_variant_id`: a line can be changed later
(quantity, variant) at the **current** price of the list (`SubscriptionService::updateItem`).

## Which price list

`PriceList::forCustomer($user)`: the list of the customer's company (`companies.pricelist_id`), else the list of the
company's role (`price_lists.role`), else the default list. A row missing from a list falls back to the default list
(`PriceList::itemFor($productId, $variantId)`).
