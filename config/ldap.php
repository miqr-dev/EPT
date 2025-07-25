<?php

return [
    'connections' => [
    'default' => [
        'hosts' => ['80.151.31.156'],
        'username' => 'miqr\\Talent-LDAPS', // or your bind DN
        'password' => 'M546W9uDqT',
        'port' => 636, // or 636 for LDAPS
        'base_dn' => 'dc=miqr,dc=local',
        'timeout' => 5,
        'use_ssl' => true, // true for LDAPS
        'use_tls' => false, // true for LDAPS
        'use_sasl' => false,
    ],
],

    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'info'),
    ],

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],
    'options' => [
    // Accept self-signed certs (for testing only)
    LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_NEVER,
],

];
