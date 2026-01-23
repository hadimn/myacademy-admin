<?php

return [
    'provider' => 'gemini',

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'max_tokens' => env('GEMINI_MAX_TOKENS', 600),
        'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    ],
];
