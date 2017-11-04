<?php

namespace Kurt\Repoist\Repositories\Eloquent\Criteria;

use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

class ByUser implements CriterionInterface
{
    /**
     * User id.
     *
     * @var integer
     */
    protected $userId;

    /**
     * @param $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply the query filtering.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($entity)
    {
        return $entity->where('user_id', $this->userId);
    }
}
