<?php

/*
|--------------------------------------------------------------------------
| Shop Module Workflows
|--------------------------------------------------------------------------
|
|  dump your workflow diagrams with:
|
|  php artisan workflow:dump order --class App\\Modules\\Shop\\Models\\Order --path="public/workflows/diagrams/" --format=svg
|
*/

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderItemAssignment;

return [
    'order'   => [
        'type'          => 'state_machine',
        'marking_store' => [
            'type'      => 'single_state',
            'property'  => 'status'
        ],
        'initial_marking' => 'new',
        'supports'      => [
            Order::class
        ],
        'places' => [
            'new' => [
                'metadata' => [
                    'label' => 'new',
                ]
            ],

            'cancelled' => [
                'metadata' => [
                    'label' => 'cancelled',
                    'final' => true,
                ]
            ],
            'pending_payment' => [
                'metadata' => [
                    'label' => 'pending_payment',
                ]
            ],
            'payment_verification' => [
                'metadata' => [
                    'label' => 'payment verification',
                ]
            ],
            'payment_failed' => [
                'metadata' => [
                    'label' => 'payment failed',
                ]
            ],
            'payment_done' => [
                'metadata' => [
                    'label' => 'payment done',
                ]
            ],
            'in_process' => [
                'metadata' => [
                    'label' => 'order in process',
                ]
            ],
            'shipped' => [
                'metadata' => [
                    'label' => 'shipped',
                ]
            ],
            'completed' => [
                'metadata' => [
                    'label' => 'order completed',
                    'final' => true,

                ]
            ],
        ],
        'transitions'   => [
            'pay_order' => [
                'from' => ['new'],
                'to'   => 'pending_payment',
                'metadata' => [
                    'label' => 'pay order',
                ]
            ],
            'check_payment' => [
                'from' => ['pending_payment'],
                'to'   => 'payment_verification',
                'metadata' => [
                    'label' => 'check payment',
                ]
            ],
            'payment_done' => [
                'from' => ['payment_verification'],
                'to'   => 'payment_done',
                'metadata' => [
                    'label' => 'payment done',
                ]
            ],
            'payment_failed' => [
                'from' => ['payment_verification'],
                'to'   => 'payment_failed',
                'metadata' => [
                    'label' => 'payment failed',
                ]
            ],

            'cancel_order' => [
                'from' => ['new','pending_payment', 'payment_verification', 'payment_failed'],
                'to'   => 'cancelled',
                'metadata' => [
                    'label' => 'cancel order',
                ]
            ],

            'process_order' => [
                'from' => ['payment_done'],
                'to'   => 'in_process',
                'metadata' => [
                    'label' => 'process order',
                ]
            ],

            // Physical goods: assigned by the operator, then shipped (carrier + tracking in a modal);
            // hidden for orders of services only (see OrderWorkflowSubscriber::onGuardShipOrder).
            'ship_order' => [
                'from' => ['in_process'],
                'to'   => 'shipped',
                'metadata' => [
                    'label' => 'ship order',
                    'action' => 'shipOrder',
                ]
            ],

            'complete_order' => [
                'from' => ['in_process', 'shipped'],
                'to'   => 'completed',
                'metadata' => [
                    'label' => 'complete order',
                ]
            ],
        ],

    ],

    'order_item_assignment'   => [
        'type'          => 'state_machine',
        'marking_store' => [
            'type'      => 'single_state',
            'property'  => 'status'
        ],
        'initial_marking' => 'pending',
        'supports'      => [
            OrderItemAssignment::class
        ],
        'places' => [
            'pending' => [
                'metadata' => [
                    'label' => 'pending',
                ]
            ],
            'assigned' => [
                'metadata' => [
                    'label' => 'assigned',
                    'final' => true,
                ]
            ],
            'generated' => [
                'metadata' => [
                    'label' => 'assigned',
                    'final' => true,
                ]
            ],
        ],
        'transitions'   => [
            'assign' => [
                'from' => ['pending'],
                'to'   => 'assigned',
                'metadata' => [
                    'label' => 'assign item',
                    'action' => 'assignItem'
                ]
            ],
            'generate' => [
                'from' => ['pending'],
                'to'   => 'generated',
                'metadata' => [
                    'label' => 'generate service & license',
                    'action' => 'generateServiceAndLicense'
                ]
            ],
        ]
    ],
    // A sold service: the driver (Provisioner) runs inside each transition.
    'service_item' => [
        'type'          => 'state_machine',
        'marking_store' => ['type' => 'single_state', 'property' => 'status'],
        'initial_marking' => 'new',
        'supports'      => [\App\Modules\Shop\Models\ServiceItem::class],
        'places' => [
            'new'        => ['metadata' => ['label' => 'new']],
            'active'     => ['metadata' => ['label' => 'active']],
            'suspended'  => ['metadata' => ['label' => 'suspended']],
            'terminated' => ['metadata' => ['label' => 'terminated', 'final' => true]],
        ],
        'transitions' => [
            'provision' => ['from' => ['new'],                        'to' => 'active',     'metadata' => ['label' => 'provision']],
            'suspend'   => ['from' => ['active'],                     'to' => 'suspended',  'metadata' => ['label' => 'suspend', 'class' => 'warning']],
            'resume'    => ['from' => ['suspended'],                  'to' => 'active',     'metadata' => ['label' => 'resume']],
            'terminate' => ['from' => ['new', 'active', 'suspended'], 'to' => 'terminated', 'metadata' => ['label' => 'terminate', 'class' => 'danger']],
        ],
    ],

    'subscription' => [
        'type'          => 'state_machine',
        'marking_store' => ['type' => 'single_state', 'property' => 'status'],
        'initial_marking' => 'pending',
        'supports'      => [\App\Modules\Shop\Models\Subscription::class],
        'places' => [
            'pending'   => ['metadata' => ['label' => 'pending']],
            'trialing'  => ['metadata' => ['label' => 'trial']],
            'active'    => ['metadata' => ['label' => 'active']],
            'past_due'  => ['metadata' => ['label' => 'past due']],
            'cancelled' => ['metadata' => ['label' => 'cancelled', 'final' => true]],
        ],
        'transitions' => [
            'activate'   => ['from' => ['pending', 'trialing', 'past_due'], 'to' => 'active',    'metadata' => ['label' => 'activate']],
            'past_due'   => ['from' => ['active'],                          'to' => 'past_due',  'metadata' => ['label' => 'mark past due']],
            'cancel'     => ['from' => ['pending', 'trialing', 'active', 'past_due'], 'to' => 'cancelled', 'metadata' => ['label' => 'cancel']],
        ],
    ],
];
