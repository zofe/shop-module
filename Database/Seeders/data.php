<?php

/*
| Demo catalogue: a small IT shop for businesses. It shows every way the shop sells:
| - physical products with a serial number (assigned, shipped, then owned by the customer),
| - services bought once (provisioned at payment, with a licence),
| - services sold as a fee (subscriptions: monthly / yearly, activation, trial, bundle).
*/
return [
    'categories' => [
        ['id' => 1, 'name' => 'Hardware',  'slug' => 'hardware',  'parent_id' => null, 'order' => 0],
        ['id' => 2, 'name' => 'Services',  'slug' => 'services',  'parent_id' => null, 'order' => 1],
        ['id' => 3, 'name' => 'Plans',     'slug' => 'plans',     'parent_id' => null, 'order' => 2],
    ],

    'products' => [
        // ── physical products: units with a serial number ──
        [
            'id'          => 1,
            'name'        => 'Business Laptop 14"',
            'slug'        => 'business-laptop-14',
            'sku'         => 'HW-LAPTOP',
            'image'       => 'demo-laptop',
            'type'        => 'inventory_item',
            'category_id' => 1,
            'description' => 'Ultrabook 14" for office work: 16 GB RAM, 512 GB SSD, 3 years on-site warranty. Each unit has a serial number: it is assigned to the order, shipped and registered to the customer.',
        ],
        [
            'id'          => 2,
            'name'        => 'Multifunction Printer',
            'slug'        => 'multifunction-printer',
            'sku'         => 'HW-PRINTER',
            'image'       => 'demo-printer',
            'type'        => 'inventory_item',
            'category_id' => 1,
            'description' => 'Colour laser printer, scanner and copier for a small office. Serial number tracked from the stock to the customer.',
        ],
        // ── services bought once ──
        [
            'id'          => 3,
            'name'        => 'On-site Setup',
            'slug'        => 'on-site-setup',
            'sku'         => 'SV-SETUP',
            'image'       => 'demo-setup',
            'type'        => 'service_item',
            'category_id' => 2,
            'description' => 'A technician installs and configures your devices on site: network, accounts, printers. One service item per device, provisioned as soon as the order is paid.',
        ],
        [
            'id'          => 4,
            'name'        => 'Extended Warranty',
            'slug'        => 'extended-warranty',
            'sku'         => 'SV-WARRANTY',
            'image'       => 'demo-warranty',
            'type'        => 'service_item',
            'category_id' => 2,
            'description' => 'Two more years of coverage on a device. Bought once, it becomes a licence with an expiry date.',
        ],
        // ── services sold as a fee (subscriptions) ──
        [
            'id'          => 5,
            'name'        => 'Remote Assistance',
            'slug'        => 'remote-assistance',
            'sku'         => 'PL-ASSIST',
            'image'       => 'demo-assistance',
            'type'        => 'service_item',
            'category_id' => 3,
            'description' => 'Help desk by phone and remote access, business hours, 4h response. Monthly or yearly fee with a one-off activation.',
        ],
        [
            'id'          => 6,
            'name'        => 'Antivirus & Updates',
            'slug'        => 'antivirus-updates',
            'sku'         => 'PL-SECURE',
            'image'       => 'demo-antivirus',
            'type'        => 'service_item',
            'category_id' => 3,
            'description' => 'Endpoint protection and managed updates on every device of the office. Monthly fee, 14 days free trial.',
        ],
        [
            'id'          => 7,
            'name'        => 'Office Care',
            'slug'        => 'office-care',
            'sku'         => 'PL-CARE',
            'image'       => 'demo-care',
            'type'        => 'bundle',
            'category_id' => 3,
            'description' => 'Remote Assistance and Antivirus & Updates together, one fee lower than the sum.',
        ],
    ],

    'product_variants' => [
        // metadata: the attributes of the variant, shown with the product
        ['id' => 1, 'product_id' => 1, 'name' => '16 GB / 512 GB', 'sku' => 'HW-LAPTOP-16',  'stock' => 10, 'metadata' => ['ram' => '16 GB', 'storage' => '512 GB SSD']],
        ['id' => 2, 'product_id' => 1, 'name' => '32 GB / 1 TB',   'sku' => 'HW-LAPTOP-32',  'stock' => 5,  'metadata' => ['ram' => '32 GB', 'storage' => '1 TB SSD']],
        ['id' => 3, 'product_id' => 4, 'name' => 'Laptop',         'sku' => 'SV-WARRANTY-L', 'stock' => 0,  'metadata' => ['covers' => 'laptop', 'years' => '2']],
        ['id' => 4, 'product_id' => 4, 'name' => 'Printer',        'sku' => 'SV-WARRANTY-P', 'stock' => 0,  'metadata' => ['covers' => 'printer', 'years' => '2']],
    ],

    'product_bundle_items' => [
        ['bundle_product_id' => 7, 'product_id' => 5, 'qty' => 1],
        ['bundle_product_id' => 7, 'product_id' => 6, 'qty' => 1],
    ],

    'price_lists' => [
        ['id' => 1, 'name' => 'Standard', 'is_default' => 1, 'is_active' => 1],
    ],

    'price_list_items' => [
        // hardware, bought once (the laptop in two configurations)
        ['id' => 1, 'price_list_id' => 1, 'product_id' => 1, 'product_variant_id' => 1, 'has_onetime_payment' => 1, 'price_onetime' => 1190.00],
        ['id' => 2, 'price_list_id' => 1, 'product_id' => 1, 'product_variant_id' => 2, 'has_onetime_payment' => 1, 'price_onetime' => 1590.00],
        ['id' => 3, 'price_list_id' => 1, 'product_id' => 2, 'has_onetime_payment' => 1, 'price_onetime' => 349.00],
        // services bought once
        // metadata of a price list row: the parameters of the service, handed to the provisioning driver
        ['id' => 4, 'price_list_id' => 1, 'product_id' => 3, 'has_onetime_payment' => 1, 'price_onetime' => 120.00, 'metadata' => ['devices' => '1', 'hours' => '2']],
        ['id' => 5, 'price_list_id' => 1, 'product_id' => 4, 'product_variant_id' => 3, 'has_onetime_payment' => 1, 'price_onetime' => 149.00],
        ['id' => 6, 'price_list_id' => 1, 'product_id' => 4, 'product_variant_id' => 4, 'has_onetime_payment' => 1, 'price_onetime' => 59.00],
        // plans: monthly or yearly with an activation; monthly with a trial; the bundle
        ['id' => 7, 'price_list_id' => 1, 'product_id' => 5, 'fee_canbe_monthly' => 1, 'fee_monthly' => 29.00, 'fee_canbe_yearly' => 1, 'fee_yearly' => 290.00,
         'has_activation_price' => 1, 'price_activation' => 49.00, 'trial_days' => 0, 'metadata' => ['response' => '4h', 'hours_per_month' => '2']],
        ['id' => 8, 'price_list_id' => 1, 'product_id' => 6, 'fee_canbe_monthly' => 1, 'fee_monthly' => 9.90, 'trial_days' => 14, 'metadata' => ['devices' => '5']],
        ['id' => 9, 'price_list_id' => 1, 'product_id' => 7, 'fee_canbe_monthly' => 1, 'fee_monthly' => 34.90],
    ],

    // stock: units with a serial number, assigned to orders by the operator
    'inventory_items' => [
        ['id' => 'a1c3e5f7-1b2d-4c6e-8f9a-0b1c2d3e4f01', 'product_id' => 1, 'serial_number' => 'LT-24-0001', 'status' => 'in_stock', 'qty' => 1],
        ['id' => 'a1c3e5f7-1b2d-4c6e-8f9a-0b1c2d3e4f02', 'product_id' => 1, 'serial_number' => 'LT-24-0002', 'status' => 'in_stock', 'qty' => 1],
        ['id' => 'a1c3e5f7-1b2d-4c6e-8f9a-0b1c2d3e4f03', 'product_id' => 1, 'serial_number' => 'LT-24-0003', 'status' => 'in_stock', 'qty' => 1],
        ['id' => 'b2d4f6a8-2c3e-4d7f-9a0b-1c2d3e4f5a01', 'product_id' => 2, 'serial_number' => 'PR-24-0001', 'status' => 'in_stock', 'qty' => 1],
        ['id' => 'b2d4f6a8-2c3e-4d7f-9a0b-1c2d3e4f5a02', 'product_id' => 2, 'serial_number' => 'PR-24-0002', 'status' => 'in_stock', 'qty' => 1],
    ],

    // Three orders, one per road, all "new": the demo pays them and walks the workflow.
    // Fixed uuids so the seeder is idempotent.
    'orders' => [
        [
            'id'            => '9d4c1b7e-3a52-4f8e-9b1d-2e6f7a8c9d01',
            'price_list_id' => 1,
            'discount'      => 0,
            'subtotal'      => 1190.00,
            'tax'           => 261.80,
            'shipping'      => 0,
            'total'         => 1451.80,
            'status'        => 'new',
            'note'          => 'Physical product: assign a serial number, ship, complete.',
        ],
        [
            'id'            => '2f8a6c3d-7b19-4e4a-8c5d-9a1b3e7f6c02',
            'price_list_id' => 1,
            'discount'      => 0,
            'subtotal'      => 149.00,
            'tax'           => 32.78,
            'shipping'      => 0,
            'total'         => 181.78,
            'status'        => 'new',
            'note'          => 'Service: generated and provisioned when the payment is done, with its licence.',
        ],
        [
            'id'            => '7b3e9f1a-c46d-4d2b-a1e8-5c7d9b2f4e03',
            'price_list_id' => 1,
            'discount'      => 0,
            'subtotal'      => 1310.00,
            'tax'           => 288.20,
            'shipping'      => 0,
            'total'         => 1598.20,
            'status'        => 'new',
            'note'          => 'Both: the setup is provisioned at payment, the laptop is assigned and shipped.',
        ],
    ],

    'order_items' => [
        [
            'order_id'           => '9d4c1b7e-3a52-4f8e-9b1d-2e6f7a8c9d01',
            'price_list_item_id' => 1,
            'product_variant_id' => 1,
            'prd_code'           => 'HW-LAPTOP-16',
            'name'               => 'Business Laptop 14" — 16 GB / 512 GB',
            'qty'                => 1,
            'price'              => 1190.00,
            'subtotal'           => 1190.00,
            'discountRate'       => 0,
            'taxRate'            => 22.00,
            'shipping'           => 0,
            'deliverable_type'   => 'inventory_item',
        ],
        [
            'order_id'           => '2f8a6c3d-7b19-4e4a-8c5d-9a1b3e7f6c02',
            'price_list_item_id' => 5,
            'product_variant_id' => 3,
            'prd_code'           => 'SV-WARRANTY-L',
            'name'               => 'Extended Warranty — Laptop',
            'qty'                => 1,
            'price'              => 149.00,
            'subtotal'           => 149.00,
            'discountRate'       => 0,
            'taxRate'            => 22.00,
            'shipping'           => 0,
            'deliverable_type'   => 'service_item',
        ],
        [
            'order_id'           => '7b3e9f1a-c46d-4d2b-a1e8-5c7d9b2f4e03',
            'price_list_item_id' => 1,
            'product_variant_id' => 1,
            'prd_code'           => 'HW-LAPTOP-16',
            'name'               => 'Business Laptop 14" — 16 GB / 512 GB',
            'qty'                => 1,
            'price'              => 1190.00,
            'subtotal'           => 1190.00,
            'discountRate'       => 0,
            'taxRate'            => 22.00,
            'shipping'           => 0,
            'deliverable_type'   => 'inventory_item',
        ],
        [
            'order_id'           => '7b3e9f1a-c46d-4d2b-a1e8-5c7d9b2f4e03',
            'price_list_item_id' => 4,
            'prd_code'           => 'SV-SETUP',
            'name'               => 'On-site Setup',
            'qty'                => 1,
            'price'              => 120.00,
            'subtotal'           => 120.00,
            'discountRate'       => 0,
            'taxRate'            => 22.00,
            'shipping'           => 0,
            'deliverable_type'   => 'service_item',
        ],
    ],
];
