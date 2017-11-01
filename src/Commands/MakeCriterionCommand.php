<?php

namespace Kurt\Repoist\Commands;

use Illuminate\Console\Command;

class MakeCriterionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:criterion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new criterion';

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
        $this->appNamespace = $this->laravel->getNamespace();

        $this->createCriterion();
    }
    
    private function createCriterion()
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
}
