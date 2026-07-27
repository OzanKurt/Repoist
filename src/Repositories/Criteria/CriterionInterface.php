<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface CriterionInterface
{
    /**
     * Apply the criterion to the given query.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder;
}
