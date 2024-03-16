<?php

return [
    'menu' => [
        /* Name For Showing In Menu => [Props] {{ Except 'models' }} */
        'Dashboard' => [],
        'models' => [
            /* Name For Showing In Menu => model */
            'Users' => App\Models\User::class
        ]
    ],
    'models' => [
        /* model => [Props] */
        'App\Models\User' => [
            /* edit, new, delete */
            'edit' => false,
            'new' => false,
            /* 'view' => ['create' => 'Your Custom View for the create route'] */
            'columns' => [
                /* ``default for all column``
                 * column => 'shown,writable'  {{ writable: encounters when a new one is created or edited }} */
                /*
                 * column => 'hidden' {{ if want to hide }}
                 * column => 'non-writable' {{ if don't want to allow to be inputed in the form }} [DOESN'T WORK IF EDIT AND NEW BOTH ARE FALSE]
                 * column => 'disabled' {{ it will be shown but will be disabled to be inputed }} [DOESN'T WORK IF EDIT IS FALSE]
                */
                'password' => 'hidden',
                'remember_token' => 'hidden',
            ]
        ]
    ]
];
