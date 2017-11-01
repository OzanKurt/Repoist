<?php

namespace %namespaces.repositories%\Criteria;

use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

class %criterion% implements CriterionInterface
{
    protected $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * Apply the query filtering.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($entity)
    {
        return $entity->where('field', $this->field);
    }
}
