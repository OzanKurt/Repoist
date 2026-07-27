<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class WhereIn implements CriterionInterface
{
    /**
     * @param  array<int, mixed>  $values
     */
    public function __construct(
        protected string $column,
        protected array $values,
    ) {}

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereIn($this->column, $this->values);
    }
}
