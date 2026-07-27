<?php

declare(strict_types=1);

namespace Kurt\Repoist\Tests\Stubs;

use Kurt\Repoist\Repositories\Eloquent\AbstractRepository;
use Kurt\Repoist\Repositories\Eloquent\Concerns\Cacheable;

final class CachedPostRepository extends AbstractRepository
{
    use Cacheable;

    public function entity(): string
    {
        return Post::class;
    }
}
