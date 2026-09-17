<?php

/*
|--------------------------------------------------------------------------
| Shop Module Configuration
|--------------------------------------------------------------------------
| Layout, menus and the permissions the shop adds to rapyd-admin (merged into
| config auth.permissions / auth.role_permissions: the AuthSeeder creates them).
*/
return [
    'layout' => 'shop::admin',
    'menu_admin' => 'shop::admin_menu',
    'menu_admin_position' => 0,
    'menu_frontend' => 'shop::frontend_menu',
    'menu_frontend_position' => 0,

    'permissions' => [
        // back office (the pages check "admin|edit x|view x")
        'view products', 'edit products', 'view categories', 'edit categories', 'view prices', 'edit prices', 'edit price lists',
        'view orders', 'edit orders', 'view subscriptions', 'edit subscriptions',
        'view inventory items', 'edit inventory items', 'view service items', 'edit service items',
        // the customer: their own orders and subscriptions, and paying them
        'view own orders', 'edit own orders', 'pay own orders',
    ],
    'role_permissions' => [
        'operator' => [
            'view products', 'edit products', 'view categories', 'edit categories', 'view prices', 'edit prices', 'edit price lists',
            'view orders', 'edit orders', 'view subscriptions', 'edit subscriptions',
            'view inventory items', 'edit inventory items', 'view service items', 'edit service items',
        ],
        'customer' => ['view own orders', 'edit own orders', 'pay own orders'],
        'tier1'    => ['view own orders', 'edit own orders', 'pay own orders'],
        'tier2'    => ['view own orders', 'edit own orders', 'pay own orders'],
    ],
];
