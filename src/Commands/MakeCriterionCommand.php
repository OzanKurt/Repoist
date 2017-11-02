<?php

namespace Kurt\Repoist\Commands;

class MakeCriterionCommand extends RepoistCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:criterion {criterion}';

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
        $this->createCriterion();
    }

    /**
     * Create a new criterion
     */
    protected function createCriterion()
    {
        $content = $this->fileManager->get(
        	__DIR__.'/../stubs/Eloquent/Criteria/Example.php'
        );

        $criterion = $this->argument('criterion');

        $replacements = [
            '%namespaces.repositories%' => $this->config('namespaces.repositories'),
            '%criterion%' => $criterion,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $fileName = $criterion;
        $fileDirectory = app_path($this->config('paths.repositories').'Criteria');
        $filePath = $fileDirectory.'/'.$fileName.'.php';

        if (!$this->fileManager->exists($fileDirectory)) {
        	$this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
        	$response = $this->ask("The criterion [{$fileName}] already exists. Do you want to overwrite it?", "Yes");

            if ($response != "Yes") {
                $this->line("The criterion [{$fileName}] will not be overwritten.");
            	return;
            }

        	$this->fileManager->put($filePath, $content);
        } else {
        	$this->fileManager->put($filePath, $content);
        }

        $this->line("The criterion [{$fileName}] has been created.");
    }
}
