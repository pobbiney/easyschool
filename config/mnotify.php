<?php

return [
    'api_key' => env('MNOTIFY_API_KEY'),
    'sender_id' => env('MNOTIFY_SENDER_ID', 'EASYSCHOOL'),
    'base_url' => env('MNOTIFY_BASE_URL', 'https://api.mnotify.com/api'),
    'enabled' => env('MNOTIFY_ENABLED', true),
];
