<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

final class Search implements CriterionInterface
{
    /**
     * @var array<int, string>
     */
    protected array $columns;

    /**
     * @param  array<int, string>|string  $columns
     */
    public function __construct(array|string $columns, protected string $term)
    {
        $this->columns = (array) $columns;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            foreach ($this->columns as $column) {
                $query->orWhere($column, 'like', '%'.$this->term.'%');
            }
        });
    }
}
