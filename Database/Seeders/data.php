<?php

return [
    'categories' => [
        [
            'id'    => 1,
            'name'  => 'Software',
            'slug'  => 'software',
            'parent_id' => null,
            'order' => 0,
        ],
        [
            'id'    => 2,
            'name'  => 'Support & Services',
            'slug'  => 'support-services',
            'parent_id' => null,
            'order' => 1,
        ],
    ],

    'products' => [
        [
            'id'          => 1,
            'name'        => 'Rapyd Admin — Professional License',
            'slug'        => 'rapyd-admin-professional',
            'sku'         => 'RPD-PRO',
            'image'       => 'demo-license',
            'type'        => 'inventory_item',
            'category_id' => 1,
            'description' => 'Licenza perpetua per un singolo progetto. Include moduli Auth, Companies e Layout. Aggiornamenti inclusi per 12 mesi.',
        ],
        [
            'id'          => 2,
            'name'        => 'Priority Support',
            'slug'        => 'priority-support',
            'sku'         => 'RPD-SUP',
            'image'       => 'demo-support',
            'type'        => 'service_item',
            'category_id' => 2,
            'description' => 'Supporto prioritario via email e ticket con SLA 24h lavorative. Include 2 ore di consulenza tecnica al mese.',
        ],
        [
            'id'          => 3,
            'name'        => 'Managed Backups',
            'slug'        => 'managed-backups',
            'sku'         => 'RPD-BKP',
            'type'        => 'service_item',
            'category_id' => 2,
            'description' => 'Backup giornaliero del database e dei file, conservazione 30 giorni, ripristino su richiesta.',
        ],
        [
            'id'          => 4,
            'name'        => 'Care Bundle',
            'slug'        => 'care-bundle',
            'sku'         => 'RPD-CARE',
            'type'        => 'bundle',
            'category_id' => 2,
            'description' => 'Priority Support e Managed Backups insieme, a un canone unico più basso della somma.',
        ],
    ],

    'product_variants' => [
        ['id' => 1, 'product_id' => 1, 'name' => '5 users',  'sku' => 'RPD-PRO-5',  'stock' => 100],
        ['id' => 2, 'product_id' => 1, 'name' => '20 users', 'sku' => 'RPD-PRO-20', 'stock' => 100],
    ],

    'product_bundle_items' => [
        ['bundle_product_id' => 4, 'product_id' => 2, 'qty' => 1],
        ['bundle_product_id' => 4, 'product_id' => 3, 'qty' => 1],
    ],

    'price_lists' => [
        [
            'id'         => 1,
            'name'       => 'Listino Standard',
            'is_default' => 1,
            'is_active'  => 1,
        ],
    ],

    'price_list_items' => [
        // the licence, bought once, in two sizes (variants)
        ['id' => 1, 'price_list_id' => 1, 'product_id' => 1, 'product_variant_id' => 1, 'has_onetime_payment' => 1, 'price_onetime' => 299.00],
        ['id' => 3, 'price_list_id' => 1, 'product_id' => 1, 'product_variant_id' => 2, 'has_onetime_payment' => 1, 'price_onetime' => 499.00],
        // the support, a fee: monthly or yearly, with an activation and a trial
        ['id' => 2, 'price_list_id' => 1, 'product_id' => 2, 'fee_canbe_monthly' => 1, 'fee_monthly' => 14.90, 'fee_canbe_yearly' => 1, 'fee_yearly' => 149.00,
         'has_activation_price' => 1, 'price_activation' => 20.00, 'trial_days' => 0],
        // backups, a monthly fee with a 14 days trial
        ['id' => 4, 'price_list_id' => 1, 'product_id' => 3, 'fee_canbe_monthly' => 1, 'fee_monthly' => 5.00, 'trial_days' => 14],
        // the bundle: 17.90 instead of 19.90
        ['id' => 5, 'price_list_id' => 1, 'product_id' => 4, 'fee_canbe_monthly' => 1, 'fee_monthly' => 17.90],
    ],

    'orders' => [
        [
            'id'            => '00000000-0000-0000-0000-000000000001',
            'price_list_id' => 1,
            'discount'      => 0,
            'subtotal'      => 299.00,
            'tax'           => 65.78,
            'shipping'      => 0,
            'total'         => 364.78,
            'status'        => 'completed',
            'note'          => null,
        ],
        [
            'id'            => '00000000-0000-0000-0000-000000000002',
            'price_list_id' => 1,
            'discount'      => 0,
            'subtotal'      => 149.00,
            'tax'           => 32.78,
            'shipping'      => 0,
            'total'         => 181.78,
            'status'        => 'new',
            'note'          => null,
        ],
    ],

    'order_items' => [
        [
            'order_id'          => '00000000-0000-0000-0000-000000000001',
            'price_list_item_id' => 1,
            'prd_code'          => 'RPD-PRO-5',
            'name'              => 'Rapyd Admin — Professional License — 5 users',
            'qty'               => 1,
            'price'             => 299.00,
            'subtotal'          => 299.00,
            'discountRate'      => 0,
            'taxRate'           => 22.00,
            'shipping'          => 0,
        ],
        [
            'order_id'          => '00000000-0000-0000-0000-000000000002',
            'price_list_item_id' => 2,
            'prd_code'          => 'RPD-SUP-YEAR',
            'name'              => 'Priority Support — Annual Plan',
            'qty'               => 1,
            'price'             => 149.00,
            'subtotal'          => 149.00,
            'discountRate'      => 0,
            'taxRate'           => 22.00,
            'shipping'          => 0,
        ],
    ],
];
