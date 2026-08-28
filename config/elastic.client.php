<?php

return [
    'default' => env('ELASTIC_CONNECTION', 'default'),

    'connections' => [
        'default' => [
            'hosts' => [
                env('ELASTIC_HOST', 'http://127.0.0.1:9200'),
            ],
            'httpClientOptions' => [
                'timeout' => 2,
            ],
        ],
    ],
];
