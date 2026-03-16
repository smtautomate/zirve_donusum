<?php

namespace ZirveDonusum;

use Illuminate\Support\ServiceProvider;
use ZirveDonusum\Mikro\MikroClient;
use ZirveDonusum\Zirve\ZirvePortalClient;

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

        // Alias'lar
        $this->app->alias(MikroClient::class, 'mikro-portal');
        $this->app->alias(ZirvePortalClient::class, 'zirve-portal');

        // Geriye uyumluluk: eski 'zirve-donusum' alias'ı default client'a yönlendir
        $this->app->singleton('zirve-donusum', function ($app) {
            $default = $app['config']['zirve_donusum.default'] ?? 'zirve';
            return $default === 'mikro'
                ? $app->make(MikroClient::class)
                : $app->make(ZirvePortalClient::class);
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
