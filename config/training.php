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
        [
            'group' => 'Verwaltung',
            'items' => [
                [
                    'label' => 'Schulungsgruppen',
                    'route' => 'training.groups.index',
                    'icon'  => 'heroicon-o-folder',
                ],
                [
                    'label' => 'Schulungen',
                    'route' => 'training.trainings.index',
                    'icon'  => 'heroicon-o-academic-cap',
                ],
                [
                    'label' => 'Schulungstermine',
                    'route' => 'training.sessions.index',
                    'icon'  => 'heroicon-o-calendar-days',
                ],
                [
                    'label' => 'Referenten',
                    'route' => 'training.instructors.index',
                    'icon'  => 'heroicon-o-user-group',
                ],
                [
                    'label' => 'Teilnehmer',
                    'route' => 'training.participants.index',
                    'icon'  => 'heroicon-o-users',
                ],
            ],
        ],
    ],
];
