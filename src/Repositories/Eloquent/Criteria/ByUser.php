<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class ByUser implements CriterionInterface
{
    public function __construct(
        protected int|string $userId,
        protected string $column = 'user_id',
    ) {}

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where($this->column, $this->userId);
    }
}
