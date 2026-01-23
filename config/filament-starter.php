<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Status
    |--------------------------------------------------------------------------
    */
    'installed' => false,

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
    'managed_panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Superadmin Strategy
    |--------------------------------------------------------------------------
    */
    'superadmin' => [
        'column' => 'is_admin',
        'value' => true,
        // Or use a closure in a provider
    ],
];
