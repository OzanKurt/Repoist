<?php

declare(strict_types=1);

namespace Kurt\Repoist\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 */
class Post extends Model
{
    protected $guarded = [];
}
