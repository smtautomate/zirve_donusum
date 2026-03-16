<?php

return [

    /*
    |--------------------------------------------------------------------------
    | eMikro Portal Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('EMIKRO_BASE_URL', 'https://eportal.mikrogrup.com'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Credentials
    |--------------------------------------------------------------------------
    */
    'email' => env('EMIKRO_EMAIL', ''),
    'password' => env('EMIKRO_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('EMIKRO_TIMEOUT', 30),
    'verify_ssl' => env('EMIKRO_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Session Cache
    |--------------------------------------------------------------------------
    | Session cookie'yi dosyada saklar, her istekte yeniden login olmamak için.
    */
    'cache_session' => env('EMIKRO_CACHE_SESSION', true),
    'session_ttl' => env('EMIKRO_SESSION_TTL', 82800), // 23 saat (cookie 24 saat geçerli)

];
