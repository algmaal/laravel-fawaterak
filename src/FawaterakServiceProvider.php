<?php

namespace Algmaal\LaravelFawaterak;

use Algmaal\LaravelFawaterak\Services\FawaterakService;
use Algmaal\LaravelFawaterak\Services\PaymentService;
use Algmaal\LaravelFawaterak\Contracts\FawaterakServiceInterface;
use Algmaal\LaravelFawaterak\Contracts\PaymentServiceInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class FawaterakServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fawaterak.php',
            'fawaterak'
        );

        // Register services
        $this->app->singleton(FawaterakServiceInterface::class, function ($app) {
            return new FawaterakService(
                $app['config']['fawaterak']
            );
        });

        $this->app->singleton(PaymentServiceInterface::class, function ($app) {
            return new PaymentService(
                $app[FawaterakServiceInterface::class]
            );
        });

        // Register aliases
        $this->app->alias(FawaterakServiceInterface::class, 'fawaterak');
        $this->app->alias(PaymentServiceInterface::class, 'fawaterak.payment');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fawaterak.php' => config_path('fawaterak.php'),
            ], 'fawaterak-config');

            // Publish migrations if any
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'fawaterak-migrations');
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load views if any
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fawaterak');

        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/fawaterak'),
            ], 'fawaterak-views');
        }

        // Register webhook routes
        $this->registerWebhookRoutes();
    }

    /**
     * Register webhook routes.
     */
    protected function registerWebhookRoutes(): void
    {
        if (config('fawaterak.webhook.enabled', true)) {
            Route::group([
                'prefix' => 'fawaterak',
                'middleware' => config('fawaterak.webhook.middleware', ['api']),
                'namespace' => 'Algmaal\LaravelFawaterak\Http\Controllers',
            ], function () {
                Route::post('webhook', 'WebhookController@handle')->name('fawaterak.webhook');
            });
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            FawaterakServiceInterface::class,
            PaymentServiceInterface::class,
            'fawaterak',
            'fawaterak.payment',
        ];
    }
}
