<?php

namespace Kurt\Repoist;

use Illuminate\Support\ServiceProvider;

class RepoistServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepoist();
    }

    /**
     * Register Repoist Commands.
     *
     * @return void
     */
    private function registerRepoist()
    {
        $this->app->singleton('command.kurt.repoist', function ($app) {
            return $app['Kurt\Repoist\Commands\RepositoryMakeCommand'];
        });

        $this->commands('command.kurt.repoist');
    }

    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/config/repoist.php' => config_path('repoist.php'),
        ]);
    }
}
