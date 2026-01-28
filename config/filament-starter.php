<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Status
    |--------------------------------------------------------------------------
    */
    'installed' => true,

    /*
    |--------------------------------------------------------------------------
    | Tenancy Module
    |--------------------------------------------------------------------------
    */
    'tenancy' => [
        'enabled' => false,
        'mode' => 'single_db', // single_db, multi_db
        'identification' => 'path', // domain, subdomain, path, header
        'tenant_model' => 'App\Models\Tenant',
        'scoped_panels' => ['admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multilanguage Module
    |--------------------------------------------------------------------------
    */
    'multilanguage' => [
        'enabled' => false,
        'default_locale' => 'en',
        'allowed_locales' => ['en', 'fr', 'es'],
        'persistence' => 'session', // session, user, tenant
    ],

    /*
    |--------------------------------------------------------------------------
    | Managed Panels
    |--------------------------------------------------------------------------
    */
    'managed_panels' => ['admin', 'staff', 'knowledge-base'],

    /*
    |--------------------------------------------------------------------------
    | Superadmin Strategy
    |--------------------------------------------------------------------------
    */
    'superadmin' => [
        'role' => 'super_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Developer Gate
    |--------------------------------------------------------------------------
    */
    'developer_gate' => [
        'enabled' => env('STARTER_DEVELOPER_GATE_ENABLED', false),
        'password' => env('STARTER_DEVELOPER_GATE_PASSWORD', 'secret'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Global Configurations
    |--------------------------------------------------------------------------
    */
    'plugins' => [
        'backgrounds' => [
            'show_attribution' => true,
            'remember' => 900,
            'image_provider' => 'curated', // curated, my-images, triangles
            'my_images_directory' => 'images/backgrounds',
        ],
        'revive' => [
            'user_scoping' => true,
            'tenant_scoping' => true,
            'global_admin_panels' => ['admin'],
        ],
    ],
];
