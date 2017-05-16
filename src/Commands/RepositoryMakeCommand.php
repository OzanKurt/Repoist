<?php
namespace Kurt\Repoist\Commands;

use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RepositoryMakeCommand extends Command
{
    use DetectsApplicationNamespace;

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
     * @var \Illuminate\Foundation\Composer | \Illuminate\Support\Composer
     */
    private $composer;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;

        if (class_exists(\Illuminate\Support\Composer::class)) {
            $this->composer = app(\Illuminate\Support\Composer::class);
        } else {
            $this->composer = app(\Illuminate\Foundation\Composer::class);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->meta['names'] = $this->generateNames();
        $this->meta['namespaces'] = $this->generateNamespaces();
        $this->meta['filenames'] = $this->generateFileNames();
        $this->meta['paths'] = $this->generatePaths();

        $this->makeRepository();
    }

    /**
     * @return array
     */
    private function generateNamespaces()
    {
        return [
            'contract' => $this->cleanNamespaces(config('repoist.paths.contract') . '/' . $this->meta['names']['subNamespace']),
            'eloquent' => $this->cleanNamespaces(config('repoist.paths.eloquent') . '/' . $this->meta['names']['subNamespace']),
            'model' => $this->cleanNamespaces(config('repoist.paths.model') . '/' . $this->meta['names']['subNamespace']),
        ];
    }

    /**
     * @return string
     */
    private function cleanNamespaces($str)
    {
        return str_replace('\\\\', '\\', str_replace('/', '\\', $str));
    }

    /**
     * @return array
     */
    private function generatePaths()
    {
        return [
            'contract' => './' . config('repoist.paths.contract') . '/' . $this->meta['names']['subPath'] . $this->meta['filenames']['contract'] . '.php',
            'eloquent' => './' . config('repoist.paths.eloquent') . '/' . $this->meta['names']['subPath'] . $this->meta['filenames']['eloquent'] . '.php',
            'model' => './' . config('repoist.paths.model') . '/' . $this->meta['names']['subPath'] . $this->meta['filenames']['model'] . '.php',
        ];
    }

    /**
     * @return array
     */
    private function generateNames()
    {
        return [
            'name' => preg_replace("/.*?([^\\\\\\/ ]*)$/", "$1", $this->argument('name')),
            'subNamespace' => preg_replace("/(.*?)(\\/?[^\\\\\\/ ]*)$/", "$1", $this->argument('name')),
            'subPath' => preg_replace("/(.*?)([^\\\\\\/ ]*)$/", "$1", $this->argument('name')),
        ];
    }

    /**
     * @return array
     */
    private function generateFileNames()
    {
        return [
            'contract' => str_replace('{name}', $this->meta['names']['name'], config('repoist.fileNames.contract')),
            'eloquent' => str_replace('{name}', $this->meta['names']['name'], config('repoist.fileNames.eloquent')),
            'model' => str_replace('{name}', $this->meta['names']['name'], config('repoist.fileNames.model')) ,
        ];
    }

    /**
     * Generate the desired repository.
     */
    protected function makeRepository()
    {
        foreach ($this->meta['paths'] as $key => $path) {
            if ($this->files->exists($path)) {
                return $this->error($this->meta['filenames'][$key] . ' already exists!');
            }
            $this->makeDirectory($path);
        }

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
            $this->files->put($this->meta['paths']['model'], $this->compileModelStub());

            $this->info($this->meta['filenames']['model'] . ' created successfully.');
        }
    }

    /**
     * Create the contract.
     */
    private function makeContract()
    {
        $this->files->put($this->meta['paths']['contract'], $this->compileContractStub());

        $this->info($this->meta['filenames']['contract'] . ' created successfully.');
    }

    /**
     * Create the eloquent repository.
     */
    private function makeEloquent()
    {
        $this->files->put($this->meta['paths']['eloquent'], $this->compileEloquentStub());

        $this->info($this->meta['filenames']['eloquent'] . ' created successfully.');
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
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileContractStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/contract.stub');

        $this->replaceNamespace($stub, 'contract')->replaceContractName($stub);

        return $stub;
    }

    /**
     * Compile the model stub.
     *
     * @return string
     */
    protected function compileModelStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/model.stub');

        $this->replaceNamespace($stub, 'model')->replaceModel($stub);

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

        $this->replaceNamespace($stub, 'eloquent')->replaceRepositoryName($stub)->replaceContractName($stub)->replaceModel($stub);

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

        $this->replaceNamespace($stub, 'eloquent')->replaceRepositoryName($stub)->replaceContractName($stub);

        return $stub;
    }

    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceNamespace(&$stub, $type)
    {
        $stub = str_replace('{{namespace}}', $this->meta['namespaces'][$type], $stub);

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
        $stub = str_replace('{{contract}}', $this->meta['filenames']['contract'], $stub);

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
        $stub = str_replace('{{class}}', $this->meta['filenames']['eloquent'], $stub);

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
        $stub = str_replace('{{model_use}}', $this->meta['namespaces']['model'] . '\\' . $this->meta['filenames']['model'], $stub);

        $stub = str_replace('{{model}}', $this->meta['filenames']['model'], $stub);

        return $this;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [['name', InputArgument::REQUIRED, 'The name of the repository.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [['model', 'm', InputOption::VALUE_NONE, 'Want a model for this repository?'],
        ];
    }
}
