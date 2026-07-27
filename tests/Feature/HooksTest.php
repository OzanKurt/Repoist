<?php

declare(strict_types=1);

use Kurt\Repoist\Tests\Stubs\HookedPostRepository;

it('fires the write hooks after create, update and delete', function (): void {
    $repository = new HookedPostRepository;

    $post = $repository->create(['title' => 'A']);
    $repository->update($post->id, ['title' => 'B']);
    $repository->delete($post->id);

    expect($repository->events)->toBe([
        'created:'.$post->id,
        'updated:'.$post->id,
        'deleted:'.$post->id,
    ]);
});

it('does not fire the update hook when nothing changes downstream', function (): void {
    $repository = new HookedPostRepository;

    $post = $repository->create(['title' => 'A']);
    $repository->events = [];

    $repository->update($post->id, ['title' => 'B']);

    expect($repository->events)->toBe(['updated:'.$post->id]);
});
