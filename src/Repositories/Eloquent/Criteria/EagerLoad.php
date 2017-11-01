<?php

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

class EagerLoad implements CriterionInterface
{
    protected $relations;

    public function __construct(array $relations)
    {
        $this->relations = $relations;
    }

    public function apply($entity)
    {
        return $entity->with($this->relations);
    }
}
