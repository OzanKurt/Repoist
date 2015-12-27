<?php

namespace Kurt\Repoist\Commands;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RepositoryMakeCommand extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository.';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Meta information for the requested migration.
     *
     * @var array
     */
    protected $meta;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param NameParser $parser
     * @param Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->meta['namespace'] = $this->generateNamespace();
        $this->meta['names'] = $this->generateNames();
        $this->meta['paths'] = $this->generatePaths();

        $this->makeRepository();
    }

    /**
     * @return mixed
     */
    private function generateNamespace()
    {
        $repoPath = config('repoist.path');
        if(!$repoPath){
            $repoPath = 'Repositories';
        }
        $namespace = str_replace('/', '\\', $this->getAppNamespace().'/'. $repoPath.'/'.$this->argument('name'));
        return str_replace('\\\\', '\\', $namespace);
    }

    /**
     * @return array
     */
    private function generatePaths()
    {
        return [
            'contract' => './app/'.str_replace('{name}', $this->argument('name'), config('repoist.path')).'/'.$this->argument('name').'/'.$this->meta['names']['contract'].'.php',
            'eloquent' => './app/'.str_replace('{name}', $this->argument('name'), config('repoist.path')).'/'.$this->argument('name').'/'.$this->meta['names']['eloquent'].'.php',
        ];
    }

    /**
     * @return array
     */
    private function generateNames()
    {
        return [
            'contract' => str_replace('{name}', $this->argument('name'), config('repoist.fileNames.contract')),
            'eloquent' => str_replace('{name}', $this->argument('name'), config('repoist.fileNames.eloquent')),
        ];
    }

    /**
     * Generate the desired repository.
     */
    protected function makeRepository()
    {
        foreach ($this->meta['paths'] as $key => $path) {
            if ($this->files->exists($path)) {
                return $this->error($this->meta['names'][$key] . ' already exists!');
            }
        }

        $this->makeDirectory($path);

        $this->makeContract();

        $this->makeEloquent();

        $this->makeModel();

        $this->composer->dumpAutoloads();
    }

    /**
     * Generate an Eloquent model, if the user wishes.
     */
    protected function makeModel()
    {
        if ($this->option('model')) {
            $modelPath = $this->getModelPath($this->argument('name'));

            if ($this->argument('name') && !$this->files->exists($modelPath)) {
                $model_path = (config('repoist.model_path') != '') ? config('repoist.model_path').'/' : '';
                $this->call('make:model', [
                    'name' => $model_path.$this->argument('name')
                ]);
            }
        }
    }

    /**
     * Create the contract.
     */
    private function makeContract()
    {
        $this->files->put($this->meta['paths']['contract'], $this->compileContractStub());

        $this->info($this->meta['names']['contract'].' created successfully.');
    }

    /**
     * Create the eloquent repository.
     */
    private function makeEloquent()
    {
        $this->files->put($this->meta['paths']['eloquent'], $this->compileEloquentStub());

        $this->info($this->meta['names']['eloquent'].' created successfully.');
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getModelPath()
    {
        $name = str_replace($this->getAppNamespace(), config('model_path'), $this->argument('name'));

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileContractStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/contract.stub');

        $this->replaceNamespace($stub)
             ->replaceContractName($stub);

        return $stub;
    }

    /**
     * Compile the eloquent stub.
     *
     * @return string
     */
    protected function compileEloquentStub()
    {
        if ($this->option('model')) {
            return $this->compileEloquentStubWithModel();
        }

        return $this->compileEloquentStubWithoutModel();
    }

    /**
     * Compile the eloquent stub with model.
     *
     * @return string
     */
    private function compileEloquentStubWithModel()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/eloquent_with_model.stub');

        $this->replaceNamespace($stub)
             ->replaceRepositoryName($stub)
             ->replaceContractName($stub)
             ->replaceModel($stub);

        return $stub;
    }

    /**
     * Compile the eloquent stub without model.
     *
     * @return string
     */
    private function compileEloquentStubWithoutModel()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/eloquent_without_model.stub');

        $this->replaceNamespace($stub)
             ->replaceRepositoryName($stub)
             ->replaceContractName($stub);

        return $stub;
    }

    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceNamespace(&$stub)
    {
        $stub = str_replace('{{namespace}}', $this->meta['namespace'], $stub);

        return $this;
    }

    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceContractName(&$stub)
    {
        $stub = str_replace('{{contract}}', $this->meta['names']['contract'], $stub);

        return $this;
    }

    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceRepositoryName(&$stub)
    {
        $stub = str_replace('{{class}}', $this->meta['names']['eloquent'], $stub);

        return $this;
    }

    /**
     * Replace the schema for the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceModel(&$stub)
    {
        $model_path = (config('repoist.model_path') != '') ? config('repoist.model_path').'\\' : '';

        $namespace = $this->getAppNamespace().$model_path.$this->argument('name');

        $stub = str_replace('{{model_use}}', $namespace, $stub);

        $stub = str_replace('{{model}}', $this->argument('name'), $stub);

        return $this;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the repository.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_NONE, 'Want a model for this repository?'],
        ];
    }
}
