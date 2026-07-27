<?php

declare(strict_types=1);

namespace Kurt\Repoist\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Eloquent\AbstractRepository;

final class HookedPostRepository extends AbstractRepository
{
    /**
     * @var array<int, string>
     */
    public array $events = [];

    public function entity(): string
    {
        return Post::class;
    }

    protected function afterCreate(Model $model): void
    {
        $this->events[] = 'created:'.$model->getKey();
    }

    protected function afterUpdate(Model $model): void
    {
        $this->events[] = 'updated:'.$model->getKey();
    }

    protected function afterDelete(Model $model): void
    {
        $this->events[] = 'deleted:'.$model->getKey();
    }
}
