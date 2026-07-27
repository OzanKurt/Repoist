<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Kurt\Repoist\Repositories\Eloquent\Criteria\ByUser;
use Kurt\Repoist\Repositories\Eloquent\Criteria\EagerLoad;
use Kurt\Repoist\Repositories\Eloquent\Criteria\Latest;
use Kurt\Repoist\Repositories\Eloquent\Criteria\OrderBy;
use Kurt\Repoist\Repositories\Eloquent\Criteria\Search;
use Kurt\Repoist\Repositories\Eloquent\Criteria\WhereIn;
use Kurt\Repoist\Repositories\Eloquent\Criteria\WithTrashed;
use Kurt\Repoist\Tests\Stubs\Post;
use Kurt\Repoist\Tests\Stubs\SoftPost;

it('adds a where clause for the given user id', function (): void {
    $query = Post::query();

    $returned = (new ByUser(5))->apply($query);

    expect($returned)->toBe($query)
        ->and($query->getQuery()->wheres)->toHaveCount(1)
        ->and($query->getBindings())->toContain(5);
});

it('allows overriding the user id column', function (): void {
    $query = Post::query();

    (new ByUser(5, 'author_id'))->apply($query);

    expect($query->getQuery()->wheres[0]['column'])->toBe('author_id');
});

it('eager loads the given relations', function (): void {
    $query = Post::query();

    (new EagerLoad(['comments', 'author']))->apply($query);

    expect(array_keys($query->getEagerLoads()))->toBe(['comments', 'author']);
});

it('accepts a single relation as a string', function (): void {
    $query = Post::query();

    (new EagerLoad('comments'))->apply($query);

    expect(array_keys($query->getEagerLoads()))->toBe(['comments']);
});

it('orders by a column and direction', function (): void {
    $query = Post::query();

    (new OrderBy('title', 'desc'))->apply($query);

    expect($query->getQuery()->orders)->toBe([
        ['column' => 'title', 'direction' => 'desc'],
    ]);
});

it('orders newest first with latest', function (): void {
    $query = Post::query();

    (new Latest)->apply($query);

    expect($query->getQuery()->orders)->toBe([
        ['column' => 'created_at', 'direction' => 'desc'],
    ]);
});

it('adds a where in clause', function (): void {
    $query = Post::query();

    (new WhereIn('id', [1, 2, 3]))->apply($query);

    expect($query->getQuery()->wheres[0]['type'])->toBe('In')
        ->and($query->getBindings())->toBe([1, 2, 3]);
});

it('searches across columns with like', function (): void {
    $query = Post::query();

    (new Search(['title', 'body'], 'php'))->apply($query);

    expect($query->getBindings())->toBe(['%php%', '%php%']);
});

it('includes soft deleted records', function (): void {
    $query = SoftPost::query();

    (new WithTrashed)->apply($query);

    expect($query->removedScopes())->toContain(SoftDeletingScope::class);
});
