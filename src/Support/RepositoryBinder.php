<?php

declare(strict_types=1);

namespace Kurt\Repoist\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

/**
 * Binds every "App\Repositories\Contracts\XRepository" interface to its
 * "App\Repositories\Eloquent\EloquentXRepository" implementation by convention.
 */
final readonly class RepositoryBinder
{
    public function __construct(
        private Application $app,
        private Filesystem $files,
    ) {}

    public function bind(): void
    {
        $directory = $this->contractsDirectory();

        if (! $this->files->isDirectory($directory)) {
            return;
        }

        $appNamespace = $this->app->getNamespace();
        $contractsNamespace = $appNamespace.$this->configString('namespaces.contracts');
        $repositoriesNamespace = $appNamespace.$this->configString('namespaces.repositories');

        foreach ($this->files->allFiles($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = $this->relativeClass($directory, $file->getPathname());
            $basename = class_basename($relative);
            $subNamespace = trim(str_replace('\\'.$basename, '', '\\'.$relative), '\\');
            $subNamespace = $subNamespace === $basename ? '' : $subNamespace;
            $prefix = $subNamespace !== '' ? '\\'.$subNamespace : '';

            $contract = $contractsNamespace.$prefix.'\\'.$basename;
            $implementation = $repositoriesNamespace.$prefix.'\\Eloquent'.$basename;

            $this->bindPair($contract, $implementation);
        }
    }

    private function bindPair(string $contract, string $implementation): void
    {
        if ($this->app->bound($contract)) {
            return;
        }

        if (! interface_exists($contract) || ! class_exists($implementation)) {
            return;
        }

        $this->app->bind($contract, $implementation);
    }

    private function contractsDirectory(): string
    {
        $path = str_replace('\\', '/', $this->configString('paths.contracts'));

        return $this->app->basePath('app/'.trim($path, '/'));
    }

    private function relativeClass(string $directory, string $pathname): string
    {
        $relative = ltrim(str_replace('\\', '/', substr($pathname, strlen($directory))), '/');

        return str_replace(['/', '.php'], ['\\', ''], $relative);
    }

    private function configString(string $key): string
    {
        $value = config('repoist.'.$key);

        return is_string($value) ? $value : '';
    }
}
