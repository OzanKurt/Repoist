<?php

declare(strict_types=1);

namespace Kurt\Repoist\Tests\Stubs;

use Kurt\Repoist\Repositories\Eloquent\AbstractRepository;

final class PostRepository extends AbstractRepository
{
    public function entity(): string
    {
        return Post::class;
    }
}
