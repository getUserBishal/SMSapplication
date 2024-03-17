<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RoyceLtd\LaravelBulkSMS\Services\LaravelBulkSMS;

class LaravelBulkSMSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
        $this->app->bind('sendsms', function(){
            return new LaravelBulkSMS();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'royceviews');
        //$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        //$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
