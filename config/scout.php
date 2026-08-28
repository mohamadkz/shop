<?php

return [
    'driver' => env('SCOUT_DRIVER', 'elastic'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => [
        'connection' => env('SCOUT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue' => env('SCOUT_QUEUE', 'scout'),
    ],

    'after_commit' => true,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,
];
