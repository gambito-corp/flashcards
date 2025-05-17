<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party Services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'mercadopago' => [
        'base_url' => env('MP_BASE_URL', 'https://api.mercadopago.com'),
        'public_key' => env('MP_PUBLIC_KEY'),
        'access_token' => env('MP_ACCESS_TOKEN'),
        'secret_key' => env('MP_SECRET_KEY'),
        'sandbox' => env('MP_SANDBOX', false),
    ],
    'ai_api' => [
        'base_url' => env('AI_API_BASE_URL', 'https://api.ai.net'),
        'token' => env('AI_API_TOKEN', ''),
    ],
    'medisearch' => [
        'base_url' => env('MEDSEARCH_BASE_URL', 'https://api.medisearch.com'),
        'token' => env('MEDSEARCH_TOKEN'),
    ],
    'openAI' => [
        'base_url' => env('OPENAI_BASE_URL', 'https://api.medisearch.com'),
        'base_url_v2' => env('AI_API_BASE_URL_V2', 'https://api.medisearch.com'),
        'token' => env('OPENAI_TOKEN'),
    ],
    'elsevier' => [
        'base_url' => env('ELSEVIER_BASE_URL', 'https://api.medisearch.com'),
        'token' => env('ELSEVIER_API_KEY'),
    ],
    'notifications' => [
        'admin_email' => env('ADMIN_EMAIL', ''),
        'admin2_email' => env('ADMIN2_EMAIL', ''),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ]
];
