
@if(Auth::user() && Auth::user()->hasRoleOrPermission('admin|view everything|edit everything|view shop|edit shop'))
<hr class="sidebar-divider">

<x-rpd::nav-dropdown icon="store" label="Shop Setup" active="/shop|/product|/pricelists">
    <x-rpd::nav-link label="Product categories" route="product_categories.tree" active="/products-categories" type="collapse-item" />
    <x-rpd::nav-link label="Products & Services" route="products.table" active="/products"  type="collapse-item" />
    <x-rpd::nav-link label="Prices" route="price_lists.default" active="/pricelists" type="collapse-item" />
</x-rpd::nav-dropdown>

<x-rpd::nav-dropdown icon="file-invoice" label="Sales & Billing" active="/orders|/subscriptions">
    <x-rpd::nav-link label="Orders" route="orders.table" active="/orders" type="collapse-item" />
    <x-rpd::nav-link label="Subscriptions" route="subscriptions.table" active="/subscriptions" type="collapse-item" />
{{--    <x-rpd::nav-link label="licenses" route="orders.table" active="/licenses" type="collapse-item" />--}}
</x-rpd::nav-dropdown>

<x-rpd::nav-dropdown icon="boxes" label="inventory & services" active="/inventory-|/service-">
    <x-rpd::nav-link label="Inventory Items" route="inventory_items.table" active="/inventory" type="collapse-item" />
    <x-rpd::nav-link label="Service Items" route="service_items.table" active="/service" type="collapse-item" />
</x-rpd::nav-dropdown>
<hr class="sidebar-divider">
@endif

