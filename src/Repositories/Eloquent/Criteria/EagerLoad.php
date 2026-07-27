<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class EagerLoad implements CriterionInterface
{
    /**
     * @var array<int, string>
     */
    protected array $relations;

    /**
     * @param  array<int, string>|string  $relations
     */
    public function __construct(array|string $relations)
    {
        $this->relations = (array) $relations;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->with($this->relations);
    }
}
