<?php

declare(strict_types=1);

namespace Kurt\Repoist\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 */
class SoftPost extends Model
{
    use SoftDeletes;

    protected $table = 'posts';

    protected $guarded = [];
}
