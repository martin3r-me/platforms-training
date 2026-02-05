<?php

return [
    'routing' => [
        'mode' => env('TRAINING_MODE', 'path'),
        'prefix' => 'training',
    ],

    'guard' => 'web',

    'navigation' => [
        'route' => 'training.dashboard',
        'icon'  => 'heroicon-o-academic-cap',
        'order' => 100,
    ],

    'sidebar' => [
        [
            'group' => 'Allgemein',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'training.dashboard',
                    'icon'  => 'heroicon-o-home',
                ],
            ],
        ],
    ],
];
