<?php

namespace Said\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        $this->publishes([
            __DIR__.'/../config/translatable.php' => config_path('translatable.php')
        ], 'translatable-config');
     
        // $this->publishes([
        //     __DIR__.'/../database/migrations/' => database_path('migrations')
        // ], 'translatable-migrations');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
    }
}