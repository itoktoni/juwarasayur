<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com'),
        'model' => env('OPENAI_MODEL', 'MiniMax-M2.7-highspeed'),
    ],

    'image' => [
        'api_key' => env('IMAGE_API_KEY', env('OPENAI_API_KEY')),
        'base_url' => env('IMAGE_BASE_URL', 'https://ark.ap-southeast.bytepluses.com/api/v3'),
        'model' => env('IMAGE_MODEL', 'seedream-4-5-251128'),
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
    ],

    'pexels' => [
        'api_key' => env('PEXELS_API_KEY'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'group_id' => env('TELEGRAM_GROUP_ID'),
        'threads' => [
            'instagram' => env('TELEGRAM_THREAD_INSTAGRAM'),
            'tiktok' => env('TELEGRAM_THREAD_TIKTOK'),
            'threads' => env('TELEGRAM_THREAD_THREADS'),
            'facebook' => env('TELEGRAM_THREAD_FACEBOOK'),
            'telegram' => env('TELEGRAM_THREAD_TELEGRAM'),
        ],
    ],

];
