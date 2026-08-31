<?php

$appName = strtolower((string) env('APP_NAME', ''));
$configuredBrand = strtolower((string) env('APP_BRAND', str_contains($appName, 'gbbr') ? 'gbbr' : 'miqr'));
$activeBrand = in_array($configuredBrand, ['miqr', 'gbbr'], true) ? $configuredBrand : 'miqr';

return [
    'active' => $activeBrand,

    'profiles' => [
        'miqr' => [
            'key' => 'miqr',
            'name' => 'MIQR',
            'entranceAnalysis' => [
                'logoSrc' => '/images/miqr-logo-grey.jpg',
                'logoAlt' => 'Mitteldeutsches Institut',
                'logoClass' => 'document-logo--miqr',
            ],
        ],

        'gbbr' => [
            'key' => 'gbbr',
            'name' => 'GBBR',
            'entranceAnalysis' => [
                'logoSrc' => '/images/gbbr-logo.svg',
                'logoAlt' => 'GBBR mbH - Gesellschaft fuer Bildung und berufliche Rehabilitation',
                'logoClass' => 'document-logo--gbbr',
            ],
        ],
    ],
];
