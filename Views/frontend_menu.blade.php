
<x-rpd::nav-item label="Shop" route="shop.list" active="/shop" />
@if(Auth::user() && Auth::user()->hasRoleOrPermission('view own orders'))
    <x-rpd::nav-item label="Orders" route="shop.orders" active="/shop-orders" />
@endif
