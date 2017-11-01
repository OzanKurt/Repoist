<?php

namespace Kurt\Repoist\Repositories\Criteria;

interface CriteriaInterface
{
    public function withCriteria(...$criteria);
}
