<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Varsayılan Client
    |--------------------------------------------------------------------------
    | Geriye uyumluluk için 'zirve-donusum' alias'ının hangi client'a düşeceği.
    | mikro | zirve | gib | parasut | logo_tiger | uyumsoft
    */
    'default' => env('EDONUSUM_DEFAULT', 'zirve'),

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

    /*
    |--------------------------------------------------------------------------
    | Paraşüt (api.parasut.com/v4)
    |--------------------------------------------------------------------------
    | OAuth2 password grant + refresh token. Endpoint pattern: /v4/{companyId}/{resource}
    | Geliştirici hesabı / OAuth uygulaması: https://api.parasut.com/oauth/applications
    */
    'parasut' => [
        'base_url' => env('PARASUT_BASE_URL', 'https://api.parasut.com/v4'),
        'auth_url' => env('PARASUT_AUTH_URL', 'https://api.parasut.com/oauth/token'),
        'username' => env('PARASUT_USERNAME', ''),
        'password' => env('PARASUT_PASSWORD', ''),
        'company_id' => env('PARASUT_COMPANY_ID', ''),
        'client_id' => env('PARASUT_CLIENT_ID', ''),
        'client_secret' => env('PARASUT_CLIENT_SECRET', ''),
        'redirect_uri' => env('PARASUT_REDIRECT_URI', 'urn:ietf:wg:oauth:2.0:oob'),
        'timeout' => env('PARASUT_TIMEOUT', 30),
        'verify_ssl' => env('PARASUT_VERIFY_SSL', true),
        'cache_token' => env('PARASUT_CACHE_TOKEN', true),
        'token_ttl' => env('PARASUT_TOKEN_TTL', 7200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo Tiger REST API (Logo Connect)
    |--------------------------------------------------------------------------
    | OAuth2 password grant + Bearer token; firmaNo + donemNo zorunlu.
    | Tipik base_url: http://localhost:32001/api/v1/  (Logo Connect lokal servis)
    */
    'logo_tiger' => [
        'base_url' => env('LOGO_TIGER_BASE_URL', 'http://localhost:32001/api/v1/'),
        'username' => env('LOGO_TIGER_USERNAME', ''),
        'password' => env('LOGO_TIGER_PASSWORD', ''),
        'firma_no' => env('LOGO_TIGER_FIRMA_NO', '1'),
        'donem_no' => env('LOGO_TIGER_DONEM_NO', '1'),
        'grant_type' => env('LOGO_TIGER_GRANT_TYPE', 'password'),
        'client_id' => env('LOGO_TIGER_CLIENT_ID', 'logo'),
        'timeout' => env('LOGO_TIGER_TIMEOUT', 30),
        'verify_ssl' => env('LOGO_TIGER_VERIFY_SSL', false),
        'cache_token' => env('LOGO_TIGER_CACHE_TOKEN', true),
        'token_ttl' => env('LOGO_TIGER_TOKEN_TTL', 1800),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uyumsoft E-Fatura / E-Arşiv / E-İrsaliye
    |--------------------------------------------------------------------------
    | Stateless: her istekte UserInfo + Basic Auth. Token yok.
    | Test: efatura-test.uyumsoft.com.tr  Prod: efatura.uyumsoft.com.tr
    */
    'uyumsoft' => [
        'base_url' => env('UYUMSOFT_BASE_URL', ''),
        'username' => env('UYUMSOFT_USERNAME', ''),
        'password' => env('UYUMSOFT_PASSWORD', ''),
        'test_mode' => env('UYUMSOFT_TEST_MODE', true),
        'timeout' => env('UYUMSOFT_TIMEOUT', 30),
        'verify_ssl' => env('UYUMSOFT_VERIFY_SSL', true),
    ],

];
