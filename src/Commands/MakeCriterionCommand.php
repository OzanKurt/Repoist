<?php

declare(strict_types=1);

namespace Kurt\Repoist\Commands;

final class MakeCriterionCommand extends RepoistCommand
{
    /**
     * @var string
     */
    protected $signature = 'make:criterion {criterion : The criterion name (e.g. Completed or Blog/Published)}
                            {--force : Overwrite existing files without asking}';

    /**
     * @var string
     */
    protected $description = 'Create a new query criterion';

    public function handle(): int
    {
        $input = trim(str_replace('/', '\\', $this->stringArgument('criterion')), '\\');

        $parts = explode('\\', $input);
        $name = array_pop($parts);
        $subNamespace = implode('\\', $parts);

        $namespace = $this->appNamespace().$this->config('namespaces.repositories').'\\Criteria'
            .($subNamespace !== '' ? '\\'.$subNamespace : '');

        $content = $this->fill($this->getStub('Eloquent/Criteria/Example.stub'), [
            '{{ namespace }}' => $namespace,
            '{{ criterion }}' => $name,
        ]);

        $path = $this->appPath(
            $this->config('paths.repositories').'/Criteria/'.str_replace('\\', '/', $input).'.php'
        );

        $this->writeFile($path, $content, 'criterion');

        return self::SUCCESS;
    }
}
