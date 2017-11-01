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

    public function apply($entity)
    {
        return $entity->where('field', $this->field);
    }
}
