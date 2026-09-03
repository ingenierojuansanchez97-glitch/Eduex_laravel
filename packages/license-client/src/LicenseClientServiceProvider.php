<?php

namespace Mhquickdev\LicenseClient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Mhquickdev\LicenseClient\Services\LicenseVerificationService;

class LicenseClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../config/license.php' => config_path('license.php'),
        ], 'license-config');

        // Load views with "license" namespace
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'license');

        // Register package routes
        $this->registerRoutes();
    }

    /**
     * Register the package services.
     */
    public function register()
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/license.php', 'license'
        );

        // Bind verification service as a singleton
        $this->app->singleton(LicenseVerificationService::class, function ($app) {
            return new LicenseVerificationService();
        });
    }

    /**
     * Register package routes under web middleware.
     */
    protected function registerRoutes()
    {
        Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
            Route::get('/license/activate', [Http\Controllers\LicenseClientController::class, 'showActivationForm'])
                ->name('license.activate');
                
            Route::post('/license/activate', [Http\Controllers\LicenseClientController::class, 'activate']);
        });
    }
}
