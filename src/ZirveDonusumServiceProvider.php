<?php

namespace ZirveDonusum;

use Illuminate\Support\ServiceProvider;

class ZirveDonusumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/zirve_donusum.php', 'zirve_donusum');

        $this->app->singleton(ZirveDonusumClient::class, function ($app) {
            $config = $app['config']['zirve_donusum'];

            return new ZirveDonusumClient([
                'base_url' => $config['base_url'],
                'email' => $config['email'],
                'password' => $config['password'],
                'timeout' => $config['timeout'],
                'verify_ssl' => $config['verify_ssl'],
                'cache_session' => $config['cache_session'],
                'cache_dir' => storage_path('app/emikro'),
            ]);
        });

        $this->app->alias(ZirveDonusumClient::class, 'zirve-donusum');
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
