<?php

namespace Emmanuelsiziba\SmilePay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Emmanuelsiziba\SmilePay\Client\SmilePayClient;
use Emmanuelsiziba\SmilePay\Services\StandardCheckout;
use Emmanuelsiziba\SmilePay\Services\ExpressCheckout;
use Emmanuelsiziba\SmilePay\Services\PaymentUtility;

class SmilePayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/smilepay.php', 'smilepay');

        // Register SmilePay Client
        $this->app->singleton(SmilePayClient::class, function ($app) {
            return new SmilePayClient();
        });

        // Register SmilePay Service
        $this->app->singleton('smilepay', function ($app) {
            return new SmilePay(
                $app->make(SmilePayClient::class)
            );
        });

        // Register individual services
        $this->app->singleton(StandardCheckout::class, function ($app) {
            return new StandardCheckout($app->make(SmilePayClient::class));
        });

        $this->app->singleton(ExpressCheckout::class, function ($app) {
            return new ExpressCheckout($app->make(SmilePayClient::class));
        });

        $this->app->singleton(PaymentUtility::class, function ($app) {
            return new PaymentUtility($app->make(SmilePayClient::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/smilepay.php' => config_path('smilepay.php'),
        ], 'smilepay-config');

        // Register routes
        $this->registerRoutes();

        // Fill in default callback URLs from the app URL so apps work without extra .env config
        $this->setDefaultCallbackUrls();
    }

    /**
     * Auto-configure callback URLs using the app's base URL if not explicitly set.
     * result_url  → the package's own webhook endpoint (server-to-server from ZB)
     * return_url  → the package's return page (user redirect after hosted checkout)
     * cancel_url  → same return page (user cancelled)
     * failure_url → same return page (payment failed)
     */
    protected function setDefaultCallbackUrls(): void
    {
        if (! config('smilepay.result_url')) {
            config(['smilepay.result_url' => url('/smilepay/webhook')]);
        }
        if (! config('smilepay.return_url')) {
            config(['smilepay.return_url' => url('/smilepay/return')]);
        }
        if (! config('smilepay.cancel_url')) {
            config(['smilepay.cancel_url' => url('/smilepay/return')]);
        }
        if (! config('smilepay.failure_url')) {
            config(['smilepay.failure_url' => url('/smilepay/return')]);
        }
    }

    /**
     * Register package routes
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Get route group configuration
     *
     * @return array
     */
    protected function routeConfiguration(): array
    {
        return [
            'middleware' => config('smilepay.webhook.middleware', ['api']),
        ];
    }
}
