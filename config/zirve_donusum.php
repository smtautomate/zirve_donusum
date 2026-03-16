<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Varsayılan Client
    |--------------------------------------------------------------------------
    | 'mikro' veya 'zirve' — Facade ve singleton hangi client'ı kullanacak
    */
    'default' => env('EDONUSUM_DEFAULT', 'zirve'), // mikro, zirve, gib

    /*
    |--------------------------------------------------------------------------
    | Mikro Portal (eportal.mikrogrup.com)
    |--------------------------------------------------------------------------
    | Session-based (PHPSESSID cookie) auth, /cp/{accountId}/... endpoint yapısı
    */
    'mikro' => [
        'base_url' => env('EMIKRO_BASE_URL', 'https://eportal.mikrogrup.com'),
        'email' => env('EMIKRO_EMAIL', ''),
        'password' => env('EMIKRO_PASSWORD', ''),
        'timeout' => env('EMIKRO_TIMEOUT', 30),
        'verify_ssl' => env('EMIKRO_VERIFY_SSL', true),
        'cache_session' => env('EMIKRO_CACHE_SESSION', true),
        'session_ttl' => env('EMIKRO_SESSION_TTL', 82800),
    ],

    /*
    |--------------------------------------------------------------------------
    | Zirve Portal (yeniportal.zirvedonusum.com)
    |--------------------------------------------------------------------------
    | JWT Bearer token auth, /accounting/api/... endpoint yapısı
    */
    'zirve' => [
        'base_url' => env('ZIRVE_BASE_URL', 'https://yeniportal.zirvedonusum.com/accounting/api'),
        'username' => env('ZIRVE_USERNAME', ''),
        'password' => env('ZIRVE_PASSWORD', ''),
        'timeout' => env('ZIRVE_TIMEOUT', 30),
        'verify_ssl' => env('ZIRVE_VERIFY_SSL', true),
        'cache_token' => env('ZIRVE_CACHE_TOKEN', true),
        'token_ttl' => env('ZIRVE_TOKEN_TTL', 82800),
    ],

    /*
    |--------------------------------------------------------------------------
    | GİB e-Arşiv Portal (earsivportal.efatura.gov.tr)
    |--------------------------------------------------------------------------
    | Session token auth, form-encoded dispatch pattern
    | Test: earsivportaltest.efatura.gov.tr
    | Prod: earsivportal.efatura.gov.tr
    */
    'gib' => [
        'base_url' => env('GIB_BASE_URL', 'https://earsivportal.efatura.gov.tr'),
        'username' => env('GIB_USERNAME', ''),
        'password' => env('GIB_PASSWORD', ''),
        'test_mode' => env('GIB_TEST_MODE', false),
        'timeout' => env('GIB_TIMEOUT', 30),
        'verify_ssl' => env('GIB_VERIFY_SSL', false),
        'cache_token' => env('GIB_CACHE_TOKEN', true),
        'token_ttl' => env('GIB_TOKEN_TTL', 3600),
    ],

];
