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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'airtel' => [
        'base_url' => env('AIRTEL_BASE_URL', 'https://openapiuat.airtel.africa'),
        'client_id' => env('AIRTEL_CLIENT_ID'),
        'client_secret' => env('AIRTEL_CLIENT_SECRET'),
        'public_key' => env('AIRTEL_PUBLIC_KEY'),
        'x_signature' => env('AIRTEL_X_SIGNATURE'),
        'x_key' => env('AIRTEL_X_KEY'),
        'private_key' => env('AIRTEL_PRIVATE_KEY'),
    ],

];
