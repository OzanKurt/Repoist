<?php

namespace Kurt\Repoist\Commands;

use Artisan;
use Illuminate\Console\Command;

class MakeRepositoryCommand extends Command
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

    private $stubs = [
        'contract' => __DIR__.'/../stubs/Contracts/ExampleRepository.php',
        'repository' => __DIR__.'/../stubs/Eloquent/EloquentExampleRepository.php',
    ];

    private $fileManager;

    private $model;
    private $modelName;
    private $appNamespace;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->fileManager = app('files');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->appNamespace = $this->laravel->getNamespace();

        $this->checkModel();

        [$contract, $contractName] = $this->createContract();

        $this->createRepository($contract, $contractName);

        dd('Done!');
    }

    private function createContract()
    {
        $content = $this->fileManager->get($this->stubs['contract']);

        $replacements = [
            '%namespaces.contracts%' => $this->config('namespaces.contracts'),
            '%modelName%' => $this->modelName,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = $this->modelName.'Repository';
        $fileDirectory = app_path($this->config('paths.contracts'));
        $filePath = $fileDirectory.$fileName.'.php';

        if (!$this->fileManager->exists($fileDirectory)) {
        	$this->fileManager->makeDirectory($fileDirectory, 755, true);
        }

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
        	$response = $this->ask("The repository [{$fileName}] already exists. Do you want to overwrite it?", "Yes");

            if ($response != "Yes") {
            	return;
            }

        	$this->fileManager->put($filePath, $content);
        } else {
        	$this->fileManager->put($filePath, $content);
        }

        return [$this->config('namespaces.contracts').'\\'.$fileName, $fileName];
    }

    private function createRepository($contract, $contractName)
    {
        $content = $this->fileManager->get($this->stubs['repository']);

        $replacements = [
            '%contract%' => $contract,
            '%contractName%' => $contractName,
            '%model%' => $this->model,
            '%modelName%' => $this->modelName,
            '%namespaces.repositories%' => $this->config('namespaces.repositories'),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = 'Eloquent'.$this->modelName.'Repository';
        $fileDirectory = app_path($this->config('paths.repositories'));
        $filePath = $fileDirectory.$fileName.'.php';

        // Check if the directory exists, if not create...
        if (!$this->fileManager->exists($fileDirectory)) {
        	$this->fileManager->makeDirectory($fileDirectory, 755, true);
        }

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
        	$response = $this->ask("The repository [{$fileName}] already exists. Do you want to overwrite it?", "Yes");

            if ($response != "Yes") {
            	return;
            }
        }

    	$this->fileManager->put($filePath, $content);
    }

    /**
     * Checks the models existance.
     */
    private function checkModel()
    {
        $model = $this->appNamespace.$this->argument('model');

        $this->model = str_replace('/', '\\', $model);

        if ($this->laravel->runningInConsole()) {
            if (!class_exists($model)) {
                $response = $this->ask("Model [{$this->model}] does not exist. Would you like to create it?", "Yes");
                
                if ($response == "Yes") {
                    Artisan::call('make:model', [
                        'name' => $this->model,
                    ]);

                    $this->line("Model [{$this->model}] has been successfully created.");
                }
            }
        }

        $modelParts = explode('\\', $this->model);

        $this->modelName = array_pop($modelParts);
    }

    private function config($key)
    {
        return config('repoist.'.$key);
    }
}
