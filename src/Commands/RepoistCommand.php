<?php

declare(strict_types=1);

namespace Kurt\Repoist\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class RepoistCommand extends Command
{
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * The application's root namespace (e.g. "App\").
     */
    protected function appNamespace(): string
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Resolve a path inside the application's "app" directory.
     */
    protected function appPath(string $path = ''): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        return $this->laravel->basePath('app'.($path !== '' ? '/'.$path : ''));
    }

    /**
     * Read a stub, preferring a published copy under base_path('stubs/repoist').
     */
    protected function getStub(string $name): string
    {
        $published = $this->laravel->basePath('stubs/repoist/'.$name);

        if ($this->files->exists($published)) {
            return $this->files->get($published);
        }

        return $this->files->get(__DIR__.'/../stubs/'.$name);
    }

    /**
     * Get a value from the package configuration.
     */
    protected function config(string $key): string
    {
        $value = config('repoist.'.$key);

        return is_string($value) ? $value : '';
    }

    /**
     * Get a console argument as a string.
     */
    protected function stringArgument(string $key): string
    {
        $value = $this->argument($key);

        return is_string($value) ? $value : '';
    }

    /**
     * Replace the placeholders inside a stub.
     *
     * @param  array<string, string>  $replacements
     */
    protected function fill(string $content, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Write generated content to disk, creating directories and honouring --force.
     */
    protected function writeFile(string $path, string $content, string $label): bool
    {
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path) && ! $this->option('force')) {
            if (! $this->confirm("The {$label} [{$this->relativePath($path)}] already exists. Overwrite it?", false)) {
                $this->components->warn("Skipped {$label} [{$this->relativePath($path)}].");

                return false;
            }
        }

        $this->files->put($path, $content);
        $this->components->info("Created {$label} [{$this->relativePath($path)}].");

        return true;
    }

    /**
     * Turn an absolute path into one relative to the application base path.
     */
    protected function relativePath(string $path): string
    {
        $base = str_replace('\\', '/', $this->laravel->basePath()).'/';

        return str_replace($base, '', str_replace('\\', '/', $path));
    }

    protected function isLumen(): bool
    {
        return str_contains($this->laravel->version(), 'Lumen');
    }
}
