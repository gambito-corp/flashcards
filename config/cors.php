<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // config/cors.php - ✅ CONFIGURACIÓN CORRECTA
    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie', 'telescope*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://front.flashcard.test',  // ✅ Tu frontend
        'http://flashcard.test',
        'http://localhost:3000',
        'http://localhost:8000',
        'https://react.medbystudents.com',
        'https://doctormbs.medbystudents.com',
        'https://front.react.medbystudents.com',
        'https://pre.doctormbs.medbystudents.com'
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,


    'allowed_origins_patterns' => [],

    'exposed_headers' => [],

    'max_age' => 0,

];
