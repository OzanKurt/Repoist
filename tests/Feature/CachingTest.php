<?php

declare(strict_types=1);

use Kurt\Repoist\Repositories\Eloquent\Criteria\ByUser;
use Kurt\Repoist\Tests\Stubs\CachedPostRepository;
use Kurt\Repoist\Tests\Stubs\Post;

it('caches find results until a write flushes them', function (): void {
    $repository = new CachedPostRepository;
    $post = Post::create(['title' => 'A']);

    expect($repository->find($post->id)->title)->toBe('A');

    // Bypass the repository so the cache, not the database, is under test.
    Post::whereKey($post->id)->update(['title' => 'B']);
    expect($repository->find($post->id)->title)->toBe('A');

    // A write through the repository flushes the cache.
    $repository->update($post->id, ['title' => 'C']);
    expect($repository->find($post->id)->title)->toBe('C');
});

it('caches all results until a write flushes them', function (): void {
    $repository = new CachedPostRepository;
    Post::create(['title' => 'A']);

    expect($repository->all())->toHaveCount(1);

    Post::create(['title' => 'B']);
    expect($repository->all())->toHaveCount(1);

    $repository->create(['title' => 'C']);
    expect($repository->all())->toHaveCount(3);
});

it('bypasses the cache when criteria are queued', function (): void {
    $repository = new CachedPostRepository;
    Post::create(['title' => 'Mine', 'user_id' => 1]);
    Post::create(['title' => 'Theirs', 'user_id' => 2]);

    expect($repository->all())->toHaveCount(2)
        ->and($repository->withCriteria(new ByUser(1))->all())->toHaveCount(1);
});
