<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class WithTrashed implements CriterionInterface
{
    /**
     * Include soft-deleted records. Equivalent to the model's withTrashed(),
     * expressed via the global scope so it stays type-safe and no-ops on models
     * that do not use the SoftDeletes trait.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->withoutGlobalScope(SoftDeletingScope::class);
    }
}
