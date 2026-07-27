<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Criteria;

interface CriteriaInterface
{
    /**
     * @param  CriterionInterface|array<int, CriterionInterface>  ...$criteria
     */
    public function withCriteria(...$criteria): static;
}
