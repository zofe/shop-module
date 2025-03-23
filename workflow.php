<?php

/*
|--------------------------------------------------------------------------
| Shop Module Workflows
|--------------------------------------------------------------------------
|
|
*/
return [
    'order'   => [
        'type'          => 'state_machine',
        'marking_store' => [
            'type'      => 'single_state',
            'property'  => 'status'
        ],
        'initial_marking' => 'new',
        'supports'      => ['App\Models\Order'],
        'places'        => [
            'new' => [
                'metadata' => [
                    'label' => 'nuovo',
                ]
            ],
            'pending' => [
                'metadata' => [
                    'label' => 'in attesa',
                ]
            ],
        ],
        'transitions'   => [
            'confirm' => [
                'from' => ['new'],
                'to'   => 'confirmed',
                'metadata' => [
                    'label' => 'conferma con BONIFICO'
                ]
            ],
        ],
    ],
];
