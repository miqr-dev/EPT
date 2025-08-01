<?php

use LdapRecord\Models\ActiveDirectory\User;

return [

  'defaults' => [
    'guard' => env('AUTH_GUARD', 'web'),
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
  ],

  'guards' => [
    'web' => [
      'driver' => 'session',
      'provider' => 'users',
    ],
  ],

  'providers' => [
    'users' => [
      'driver' => 'ldap',
      'model' => User::class,
      'rules' => [],
      'scopes' => [],
      'database' => [
        'model' => App\Models\User::class,
        'sync_passwords' => false,
        'sync_attributes' => \App\Ldap\ImportUser::class,
      ],
    ],

  ],

  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
      'expire' => 60,
      'throttle' => 60,
    ],
  ],

  'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
