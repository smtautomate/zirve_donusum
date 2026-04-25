<?php

namespace ZirveDonusum;

use Illuminate\Support\ServiceProvider;
use ZirveDonusum\Mikro\MikroClient;
use ZirveDonusum\Zirve\ZirvePortalClient;
use ZirveDonusum\Gib\GibClient;
use ZirveDonusum\Parasut\ParasutClient;
use ZirveDonusum\LogoTiger\LogoTigerClient;
use ZirveDonusum\Uyumsoft\UyumsoftClient;

class ZirveDonusumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/zirve_donusum.php', 'zirve_donusum');

        // Mikro Portal client
        $this->app->singleton(MikroClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.mikro'];

            return new MikroClient([
                'base_url' => $config['base_url'],
                'email' => $config['email'],
                'password' => $config['password'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_session' => $config['cache_session'],
                'cache_dir' => storage_path('app/emikro'),
            ]);
        });

        // Zirve Portal client
        $this->app->singleton(ZirvePortalClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.zirve'];

            return new ZirvePortalClient([
                'base_url' => $config['base_url'],
                'username' => $config['username'],
                'password' => $config['password'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_token' => $config['cache_token'],
                'cache_dir' => storage_path('app/zirve-portal'),
            ]);
        });

        // GİB e-Arşiv Portal client
        $this->app->singleton(GibClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.gib'];

            return new GibClient([
                'base_url' => $config['base_url'],
                'username' => $config['username'],
                'password' => $config['password'],
                'test_mode' => $config['test_mode'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_token' => $config['cache_token'],
                'cache_dir' => storage_path('app/gib-portal'),
            ]);
        });

        // Paraşüt V4 client
        $this->app->singleton(ParasutClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.parasut'];

            return new ParasutClient([
                'base_url' => $config['base_url'],
                'auth_url' => $config['auth_url'],
                'username' => $config['username'],
                'password' => $config['password'],
                'company_id' => $config['company_id'],
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_token' => $config['cache_token'],
                'cache_dir' => storage_path('app/parasut'),
            ]);
        });

        // Logo Tiger REST API client
        $this->app->singleton(LogoTigerClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.logo_tiger'];

            return new LogoTigerClient([
                'base_url' => $config['base_url'],
                'username' => $config['username'],
                'password' => $config['password'],
                'firma_no' => $config['firma_no'],
                'donem_no' => $config['donem_no'],
                'grant_type' => $config['grant_type'],
                'client_id' => $config['client_id'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_token' => $config['cache_token'],
                'cache_dir' => storage_path('app/logo-tiger'),
            ]);
        });

        // Uyumsoft E-Fatura client
        $this->app->singleton(UyumsoftClient::class, function ($app) {
            $config = $app['config']['zirve_donusum.uyumsoft'];

            return new UyumsoftClient([
                'base_url' => $config['base_url'] ?: null,
                'username' => $config['username'],
                'password' => $config['password'],
                'test_mode' => $config['test_mode'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
            ]);
        });

        // Alias'lar
        $this->app->alias(MikroClient::class, 'mikro-portal');
        $this->app->alias(ZirvePortalClient::class, 'zirve-portal');
        $this->app->alias(GibClient::class, 'gib-portal');
        $this->app->alias(ParasutClient::class, 'parasut');
        $this->app->alias(LogoTigerClient::class, 'logo-tiger');
        $this->app->alias(UyumsoftClient::class, 'uyumsoft');

        // Geriye uyumluluk: eski 'zirve-donusum' alias'ı default client'a yönlendir
        $this->app->singleton('zirve-donusum', function ($app) {
            $default = $app['config']['zirve_donusum.default'] ?? 'zirve';
            return match ($default) {
                'mikro' => $app->make(MikroClient::class),
                'gib' => $app->make(GibClient::class),
                'parasut' => $app->make(ParasutClient::class),
                'logo_tiger' => $app->make(LogoTigerClient::class),
                'uyumsoft' => $app->make(UyumsoftClient::class),
                default => $app->make(ZirvePortalClient::class),
            };
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/zirve_donusum.php' => config_path('zirve_donusum.php'),
            ], 'zirve-donusum-config');
        }
    }
}
