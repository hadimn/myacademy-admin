<?php

return [
    'provider' => 'openai',

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 600),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],
];
