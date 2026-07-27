<?php

declare(strict_types=1);

namespace Kurt\Repoist\Commands;

final class MakeRepositoryCommand extends RepoistCommand
{
    /**
     * @var string
     */
    protected $signature = 'make:repository {model : The model name (relative to the models namespace, e.g. Task or Blog/Post)}
                            {--force : Overwrite existing files without asking}';

    /**
     * @var string
     */
    protected $description = 'Create a repository contract and its Eloquent implementation for a model';

    /**
     * Fully qualified model class (e.g. App\Models\Task).
     */
    protected string $model;

    /**
     * Short model class name (e.g. Task).
     */
    protected string $modelName;

    public function handle(): int
    {
        $this->resolveModel();

        [$contract, $contractName] = $this->createContract();

        $this->createRepository($contract, $contractName);

        return self::SUCCESS;
    }

    /**
     * Resolve the model FQCN, offering to create it when it is missing.
     */
    protected function resolveModel(): void
    {
        $input = trim(str_replace('/', '\\', $this->stringArgument('model')), '\\');

        $this->modelName = class_basename($input);
        $this->model = $this->appNamespace().$this->config('namespaces.models').'\\'.$input;

        if ($this->isLumen() || class_exists($this->model)) {
            return;
        }

        if ($this->option('force') || $this->confirm("Model [{$this->model}] does not exist. Create it?", true)) {
            $this->call('make:model', ['name' => str_replace('\\', '/', $input)]);
        }
    }

    /**
     * Create the repository contract (interface).
     *
     * @return array{0: string, 1: string}
     */
    protected function createContract(): array
    {
        $namespace = $this->appNamespace().$this->config('namespaces.contracts');
        $name = $this->modelName.'Repository';

        $content = $this->fill($this->getStub('Contracts/ExampleRepository.stub'), [
            '{{ namespace }}' => $namespace,
            '{{ modelName }}' => $this->modelName,
        ]);

        $this->writeFile(
            $this->appPath($this->config('paths.contracts').'/'.$name.'.php'),
            $content,
            'contract'
        );

        return [$namespace.'\\'.$name, $name];
    }

    /**
     * Create the Eloquent repository implementation.
     */
    protected function createRepository(string $contract, string $contractName): void
    {
        $namespace = $this->appNamespace().$this->config('namespaces.repositories');
        $name = 'Eloquent'.$this->modelName.'Repository';

        $content = $this->fill($this->getStub('Eloquent/EloquentExampleRepository.stub'), [
            '{{ namespace }}' => $namespace,
            '{{ contract }}' => $contract,
            '{{ contractName }}' => $contractName,
            '{{ model }}' => $this->model,
            '{{ modelName }}' => $this->modelName,
        ]);

        $this->writeFile(
            $this->appPath($this->config('paths.repositories').'/'.$name.'.php'),
            $content,
            'repository'
        );
    }
}
