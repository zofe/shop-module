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
            'sku'         => 'RPD-PRO-001',
            'type'        => 'inventory_item',
            'category_id' => 1,
            'description' => 'Licenza perpetua per un singolo progetto. Include moduli Auth, Companies e Layout. Aggiornamenti inclusi per 12 mesi.',
        ],
        [
            'id'          => 2,
            'name'        => 'Priority Support — Annual Plan',
            'slug'        => 'priority-support-annual',
            'sku'         => 'RPD-SUP-YEAR',
            'type'        => 'service_item',
            'category_id' => 2,
            'description' => 'Supporto prioritario via email e ticket con SLA 24h lavorative. Include 2 ore di consulenza tecnica al mese.',
        ],
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
        [
            'id'                     => 1,
            'price_list_id'          => 1,
            'product_id'             => 1,
            'price_onetime_customer' => 299.00,
            'price_yearly_customer'  => 0,
            'price_monthly_customer' => 0,
        ],
        [
            'id'                     => 2,
            'price_list_id'          => 1,
            'product_id'             => 2,
            'price_onetime_customer' => 0,
            'price_yearly_customer'  => 149.00,
            'price_monthly_customer' => 14.90,
        ],
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
            'prd_code'          => 'RPD-PRO-001',
            'name'              => 'Rapyd Admin — Professional License',
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
