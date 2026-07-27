<?php

declare(strict_types=1);

namespace Kurt\Repoist;

use Illuminate\Filesystem\Filesystem;
use Kurt\Repoist\Commands\MakeCriterionCommand;
use Kurt\Repoist\Commands\MakeRepositoryCommand;
use Kurt\Repoist\Support\RepositoryBinder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RepoistServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('repoist')
            ->hasConfigFile('repoist')
            ->hasCommands([
                MakeRepositoryCommand::class,
                MakeCriterionCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        if (config('repoist.auto_bind', true)) {
            (new RepositoryBinder($this->app, $this->app->make(Filesystem::class)))->bind();
        }
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/stubs' => base_path('stubs/repoist'),
        ], 'repoist-stubs');
    }
}
