<?php
return [
    'validator' => [
        'email' => env('PST_VALIDATOR_EMAIL'),
        'meili' => [
            'index_name' => env('PST_MEILI_INDEX_NAME'),
            'key' => env('MEILI_MASTER_KEY'),
        ],
    ],
];
