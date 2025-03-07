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
        'token' => env('MP_TOKEN'),
        'public_key' => env('MP_PUBLIC_KEY'),
        'secret_key' => env('MP_SECRET_KEY'),
        'sandbox' => env('MP_SANDBOX', false),
    ],
    'medisearch' => [
        'base_url' => env('MEDSEARCH_BASE_URL', 'https://api.medisearch.com'),
        'token' => env('MEDSEARCH_TOKEN'),
    ],

];
