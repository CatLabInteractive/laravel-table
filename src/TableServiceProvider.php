<?php

namespace CatLab\Laravel\Table;

use Illuminate\Support\ServiceProvider;

/**
 * Class TableServiceProvider
 * @package CatLab\Laravel\Table
 */
class TableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'table');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/table')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}