<?php

namespace Kurt\Repoist\Repositories\Criteria;

interface CriterionInterface
{
    public function apply($entity);
}
