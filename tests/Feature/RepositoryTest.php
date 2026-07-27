<?php

declare(strict_types=1);

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kurt\Repoist\Repositories\Eloquent\Criteria\ByUser;
use Kurt\Repoist\Repositories\Eloquent\Criteria\EagerLoad;
use Kurt\Repoist\Tests\Stubs\Post;
use Kurt\Repoist\Tests\Stubs\PostRepository;

function repository(): PostRepository
{
    return new PostRepository;
}

it('returns all records as a collection', function (): void {
    Post::create(['title' => 'First']);
    Post::create(['title' => 'Second']);

    $result = repository()->all();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

it('paginates when a page size is given', function (): void {
    Post::create(['title' => 'First']);
    Post::create(['title' => 'Second']);
    Post::create(['title' => 'Third']);

    $result = repository()->all(2);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result)->toHaveCount(2)
        ->and($result->total())->toBe(3);
});

it('finds a record by id', function (): void {
    $post = Post::create(['title' => 'Hello']);

    expect(repository()->find($post->id)->title)->toBe('Hello');
});

it('throws when a record is not found', function (): void {
    repository()->find(999);
})->throws(ModelNotFoundException::class);

it('finds records where a column matches', function (): void {
    Post::create(['title' => 'Keep', 'user_id' => 1]);
    Post::create(['title' => 'Drop', 'user_id' => 2]);

    $result = repository()->findWhere('user_id', 1);

    expect($result)->toHaveCount(1)
        ->and($result->first()->title)->toBe('Keep');
});

it('finds the first record where a column matches', function (): void {
    Post::create(['title' => 'Match', 'user_id' => 7]);

    expect(repository()->findWhereFirst('user_id', 7)->title)->toBe('Match');
});

it('finds records with a like clause across columns', function (): void {
    Post::create(['title' => 'Laravel rocks']);
    Post::create(['title' => 'Something else']);

    $result = repository()->findWhereLike('title', '%Laravel%');

    expect($result)->toHaveCount(1)
        ->and($result->first()->title)->toBe('Laravel rocks');
});

it('creates a record', function (): void {
    $post = repository()->create(['title' => 'Created']);

    expect($post->exists)->toBeTrue()
        ->and(Post::whereKey($post->id)->exists())->toBeTrue();
});

it('updates a record', function (): void {
    $post = Post::create(['title' => 'Before']);

    expect(repository()->update($post->id, ['title' => 'After']))->toBeTrue()
        ->and($post->fresh()->title)->toBe('After');
});

it('deletes a record', function (): void {
    $post = Post::create(['title' => 'Bye']);

    expect(repository()->delete($post->id))->toBeTrue()
        ->and(Post::whereKey($post->id)->exists())->toBeFalse();
});

it('applies a single criterion', function (): void {
    Post::create(['title' => 'Mine', 'user_id' => 1]);
    Post::create(['title' => 'Theirs', 'user_id' => 2]);

    $result = repository()->withCriteria(new ByUser(1))->all();

    expect($result)->toHaveCount(1)
        ->and($result->first()->title)->toBe('Mine');
});

it('applies multiple criteria passed as an array', function (): void {
    Post::create(['title' => 'Mine', 'user_id' => 1]);
    Post::create(['title' => 'Theirs', 'user_id' => 2]);

    $result = repository()->withCriteria([
        new ByUser(1),
        new EagerLoad([]),
    ])->all();

    expect($result)->toHaveCount(1);
});

it('does not leak query state across calls on a reused instance', function (): void {
    Post::create(['title' => 'One', 'user_id' => 1]);
    Post::create(['title' => 'Two', 'user_id' => 2]);

    $repository = repository();

    // A filtered call must not constrain the next unfiltered call.
    expect($repository->findWhere('user_id', 1))->toHaveCount(1)
        ->and($repository->all())->toHaveCount(2);
});

it('resets criteria after a terminal operation', function (): void {
    Post::create(['title' => 'One', 'user_id' => 1]);
    Post::create(['title' => 'Two', 'user_id' => 2]);

    $repository = repository();

    expect($repository->withCriteria(new ByUser(1))->all())->toHaveCount(1)
        ->and($repository->all())->toHaveCount(2);
});

it('finds many records by id', function (): void {
    $first = Post::create(['title' => 'First']);
    $second = Post::create(['title' => 'Second']);
    Post::create(['title' => 'Third']);

    $result = repository()->findMany([$first->id, $second->id]);

    expect($result)->toHaveCount(2);
});

it('returns the first matching record or creates it', function (): void {
    $created = repository()->firstOrCreate(['title' => 'Unique']);
    $again = repository()->firstOrCreate(['title' => 'Unique']);

    expect($created->id)->toBe($again->id)
        ->and(Post::count())->toBe(1);
});

it('updates or creates a record', function (): void {
    $post = Post::create(['title' => 'Old', 'user_id' => 1]);

    $result = repository()->updateOrCreate(['id' => $post->id], ['title' => 'New']);

    expect($result->title)->toBe('New')
        ->and(Post::count())->toBe(1);
});

it('counts and checks existence', function (): void {
    expect(repository()->exists())->toBeFalse();

    Post::create(['title' => 'One']);
    Post::create(['title' => 'Two']);

    expect(repository()->count())->toBe(2)
        ->and(repository()->exists())->toBeTrue();
});

it('plucks a column', function (): void {
    Post::create(['title' => 'Alpha']);
    Post::create(['title' => 'Beta']);

    expect(repository()->pluck('title')->all())->toBe(['Alpha', 'Beta']);
});

it('chunks records', function (): void {
    Post::create(['title' => 'One']);
    Post::create(['title' => 'Two']);
    Post::create(['title' => 'Three']);

    $seen = 0;
    repository()->chunk(2, function ($posts) use (&$seen): void {
        $seen += $posts->count();
    });

    expect($seen)->toBe(3);
});
