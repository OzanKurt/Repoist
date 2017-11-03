<?php

namespace Kurt\Repoist;

use Illuminate\Support\ServiceProvider;
use Kurt\Repoist\Commands\MakeCriterionCommand;
use Kurt\Repoist\Commands\MakeRepositoryCommand;

class RepoistServiceProvider extends ServiceProvider
{
    /**
     * Commands to be registered.
     * @var array
     */
    private $repoistCommands = [
        MakeCriterionCommand::class,
        MakeRepositoryCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    	$this->mergeConfigFrom(__DIR__.'/config/repoist.php', 'repoist');

    	$this->publishes([
	        __DIR__.'/config/repoist.php' => config_path('repoist.php')
	    ], 'repoist-config');

        $this->registerCommands();
    }

    /**
     * Registers repoist commands.
     */
    public function registerCommands()
    {
    	$this->commands($this->repoistCommands);
    }
}
