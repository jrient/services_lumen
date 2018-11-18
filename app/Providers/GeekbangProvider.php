<?php

namespace App\Providers;

use App\Console\Commands\GeekbangCommand;
use Illuminate\Support\ServiceProvider;

class GeekbangProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.geekbang.updateData', function () {
            return new GeekbangCommand();
        });

        $this->commands('command.geekbang.updateData');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.geekbang.updateData'];
    }
}