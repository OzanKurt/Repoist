<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class OrderBy implements CriterionInterface
{
    public function __construct(
        protected string $column,
        protected string $direction = 'asc',
    ) {}

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->orderBy($this->column, $this->direction);
    }
}
