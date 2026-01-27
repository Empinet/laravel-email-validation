<?php

namespace Empinet\EasyEmailApi\Providers;

use Empinet\EasyEmailApi\Clients\EasyEmailApiClient;
use Empinet\EasyEmailApi\Services\EmailValidationService;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\ServiceProvider;

class EasyEmailApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/easyemailapi.php', 'easyemailapi');

        $this->app->singleton(EasyEmailApiClient::class, function ($app) {
            return new EasyEmailApiClient($app['config']->get('easyemailapi'));
        });

        $this->app->singleton(EmailValidationService::class, function ($app) {
            return new EmailValidationService(
                $app->make(EasyEmailApiClient::class),
                $app->make(CacheManager::class),
                $app['config']->get('easyemailapi')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/easyemailapi.php' => config_path('easyemailapi.php'),
        ], 'easyemailapi-config');
    }
}
