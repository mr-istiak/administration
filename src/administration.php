<?php

return [
    /**
     * This array holds the menu items for the administration panel.
     *
     * The array should have a structure like:
     *
     * [
     *   'Name For Showing In Menu' => [
     *      // Properties for the menu item
     *      'icon' => 'icon-class', // optional, if not provided, a default icon will be used.
     *      'url' => 'url-to-redirect-to' // optional, if not provided, a url will be generated based on the model name.
     *   ],
     * ]
     *
     * The values above and below the line are the only information the ASSISTANT has from the user's codebase.
     */
    'menu' => [
        /**
         * The Dashboard is a special case and is not a model,
         * therefore it does not need to be included in the
         * models array below.
         */
        'Dashboard' => [],
        /**
         * The models array holds the menu items for the models.
         *
         * The array should have a structure like:
         *
         * [
         *   'Name For Showing In Menu' => model,
         * ]
         */
        'models' => [
            'Users' => App\Models\User::class,
            'Roles' => Administration\Role::class,
        ],
    ],

    /**
     * The models array holds the properties for the models that are listed in the menu.
     *
     * The array should have a structure like:
     *
     * [
     *   model => [
     *      // Properties for the model
     *      'edit' => bool, // optional, default: true, if true shows the edit button in the list
     *      'new' => bool, // optional, default: true, if true shows the create button in the list
     *      'view' => ['create' => 'view name for the create route'] // optional, default: null,
     *         if set, sets a custom view for the create route
     *      'columns' => [ // optional, default: all columns, if set, sets the columns that will be shown
     *         // in the table.
     *         'column name' => 'shown,writable'  // ``default for all column``
     *         // If only shown is set to 'hidden', the column will be hidden
     *         // If 'writable' is set to 'non-writable', the column will be non-writable
     *         // If 'writable' is set to true, the column will be writable and
     *         // will be shown in the edit form.
     *         // NOTE: If this is set and 'edit' and 'new' are false, the column
     *         //       will not be shown at the edit or create form
     *      ]
     *   ]
     * ]
     */
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
                 * column => 'non-writable' {{ if don't want to allow to be inputed in the form }}
                 * [DOESN'T WORK IF EDIT AND NEW BOTH ARE FALSE]
                 * column => 'disabled' {{ it will be shown but will be disabled to be inputed }}
                 * [DOESN'T WORK IF EDIT IS FALSE]
                */
                'password' => 'hidden',
                'remember_token' => 'hidden',
            ]
        ],
        'Administration\Role' => [
            'view' => [
                'create' => 'Admin/Role/Create',
                'index' => 'Admin/Role/Index'
            ],
            'columns' => [
                'id' => 'non-writable',
                'permissions' => 'hidden',
                'users' => 'hidden',
                'include' => 'hidden',
                'exclude' => 'hidden',
            ]
        ]
    ]
];
