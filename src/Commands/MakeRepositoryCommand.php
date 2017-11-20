<?php

namespace Kurt\Repoist\Commands;

use Illuminate\Support\Facades\Artisan;

class MakeRepositoryCommand extends RepoistCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';

    /**
     * Stub paths.
     *
     * @var array
     */
    protected $stubs = [
        'contract'   => __DIR__.'/../stubs/Contracts/ExampleRepository.php',
        'repository' => __DIR__.'/../stubs/Eloquent/EloquentExampleRepository.php',
    ];

    /**
     * Model with full namespace.
     *
     * @var string
     */
    protected $model;

    /**
     * Model class name.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->checkModel();


        list($contract, $contractName) = $this->createContract();

        $this->createRepository($contract, $contractName);
    }

    /**
     * Create a new contract
     */
    protected function createContract()
    {
        $content = $this->fileManager->get($this->stubs['contract']);

        $replacements = [
            '%namespaces.contracts%' => $this->appNamespace.$this->config('namespaces.contracts'),
            '%modelName%'            => $this->modelName,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName      = $this->modelName.'Repository';
        $fileDirectory = app()->basePath().'/app/'.$this->config('paths.contracts');
        $filePath      = $fileDirectory.$fileName.'.php';

        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
            $response = $this->ask("The contract [{$fileName}] already exists. Do you want to overwrite it?", 'Yes');

            if ($this->isResponsePositive($response)) {
                $this->line("The contract [{$fileName}] will not be overwritten.");
                return;
            }

            $this->fileManager->put($filePath, $content);
        } else {
            $this->fileManager->put($filePath, $content);
        }

        $this->line("The contract [{$fileName}] has been created.");

        return [$this->config('namespaces.contracts').'\\'.$fileName, $fileName];
    }

    /**
     * Create a new repository
     */
    protected function createRepository($contract, $contractName)
    {
        $content = $this->fileManager->get($this->stubs['repository']);

        $replacements = [
            '%contract%'                => $contract,
            '%contractName%'            => $contractName,
            '%model%'                   => $this->model,
            '%modelName%'               => $this->modelName,
            '%namespaces.repositories%' => $this->appNamespace.$this->config('namespaces.repositories'),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName      = 'Eloquent'.$this->modelName.'Repository';
        $fileDirectory = app()->basePath().'/app/'.$this->config('paths.repositories');
        $filePath      = $fileDirectory.$fileName.'.php';

        // Check if the directory exists, if not create...
        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
            $response = $this->ask("The repository [{$fileName}] already exists. Do you want to overwrite it?", 'Yes');

            if (!$this->isResponsePositive($response)) {
                $this->line("The repository [{$fileName}] will not be overwritten.");
                return;
            }
        }

        $this->line("The repository [{$fileName}] has been created.");

        $this->fileManager->put($filePath, $content);
    }

    /**
     * Check the models existance, create if wanted.
     */
    protected function checkModel()
    {
        $model = $this->appNamespace.$this->argument('model');

        $this->model = str_replace('/', '\\', $model);

        if (!$this->isLumen() && $this->laravel->runningInConsole()) {
            if (!class_exists($this->model)) {
                $response = $this->ask("Model [{$this->model}] does not exist. Would you like to create it?", 'Yes');

                if ($this->isResponsePositive($response)) {
                    Artisan::call('make:model', [
                        'name' => $this->model,
                    ]);

                    $this->line("Model [{$this->model}] has been successfully created.");
                } else {
                    $this->line("Model [{$this->model}] is not being created.");
                }
            }
        }

        $modelParts = explode('\\', $this->model);

        $this->modelName = array_pop($modelParts);
    }

    protected function isLumen()
    {
        return str_contains(app()->version(), 'Lumen');
    }
}
