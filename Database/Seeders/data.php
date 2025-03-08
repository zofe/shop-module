<?php

return [
    'categories' => [
        [
            'id'=> 1,
            'name'=>'Products',
            'slug'=>'products',
            'parent_id'=>null,
            'order' => 0,
        ],
        [
            'id'=> 2,
            'name'=>'Services',
            'slug'=>'services',
            'parent_id'=>null,
            'order' => 1,
        ],
    ],

    'products' => [
        [
            'id'=> 1,
            'name'=>'Product 1',
            'slug'=>'product-1',
            'sku'=>'prd-1',
            'category_id'=>1,
        ],
        [
            'id'=> 2,
            'name'=>'Product 2',
            'slug'=>'product-2',
            'sku'=>'prd-2',
            'category_id'=>1,
        ],
        [
            'id'=> 3,
            'name'=>'Service 1',
            'slug'=>'service-1',
            'sku'=>'srv-1',
            'category_id'=>2,
        ],
        [
            'id'=> 4,
            'name'=>'Service 2',
            'slug'=>'service-2',
            'sku'=>'srv-2',
            'category_id'=>2,
        ],
    ],

    'price_lists' => [
        [
            'id'=> 1,
            'name'=>'Default Price List',
            'is_default'=>1,
        ],
    ],

    'price_list_items' => [
        [
            'id'=> 1,
            'price_list_id'=>1,
            'product_id'=>1,
            'price_onetime_customer'=>100,
        ],
        [
            'id'=> 2,
            'price_list_id'=>1,
            'product_id'=>2,
            'price_onetime_customer'=>200,
        ],
        [
            'id'=> 3,
            'price_list_id'=>1,
            'product_id'=>3,
            'price_onetime_customer'=>10,
        ],
        [
            'id'=> 4,
            'price_list_id'=>1,
            'product_id'=>4,
            'price_onetime_customer'=>20,
        ],
    ],
];
