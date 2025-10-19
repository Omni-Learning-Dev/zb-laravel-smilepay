<?php

namespace YourVendor\SmilePay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use YourVendor\SmilePay\Client\SmilePayClient;
use YourVendor\SmilePay\Services\StandardCheckout;
use YourVendor\SmilePay\Services\ExpressCheckout;
use YourVendor\SmilePay\Services\PaymentUtility;

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
