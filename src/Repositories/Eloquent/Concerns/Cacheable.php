<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent\Concerns;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Caches read results and flushes them on writes.
 *
 * Use inside a repository that extends AbstractRepository. Reads are cached
 * only when no criteria are queued; paginated results are never cached. For
 * complete invalidation use a taggable cache store (array, redis, memcached);
 * on non-taggable stores only the "all" key is forgotten on write.
 */
trait Cacheable
{
    protected int $cacheTtl = 3600;

    /**
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function all(?int $paginate = null): Collection|LengthAwarePaginator
    {
        if ($paginate !== null || $this->criteria !== []) {
            return parent::all($paginate);
        }

        return $this->remember($this->cacheKey('all'), fn () => parent::all());
    }

    public function find(int|string $id): Model
    {
        if ($this->criteria !== []) {
            return parent::find($id);
        }

        return $this->remember($this->cacheKey('find', (string) $id), fn () => parent::find($id));
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function create(array $properties): Model
    {
        return tap(parent::create($properties), fn () => $this->flushCache());
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function update(int|string $id, array $properties): bool
    {
        return tap(parent::update($id, $properties), fn () => $this->flushCache());
    }

    public function delete(int|string $id): bool
    {
        return tap(parent::delete($id), fn () => $this->flushCache());
    }

    public function flushCache(): void
    {
        $store = Cache::store();

        if ($store->getStore() instanceof TaggableStore) {
            $store->tags($this->cacheTags())->flush();

            return;
        }

        $store->forget($this->cacheKey('all'));
    }

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    protected function remember(string $key, Closure $callback): mixed
    {
        $store = Cache::store();

        if ($store->getStore() instanceof TaggableStore) {
            return $store->tags($this->cacheTags())->remember($key, $this->cacheTtl, $callback);
        }

        return $store->remember($key, $this->cacheTtl, $callback);
    }

    /**
     * @return array<int, string>
     */
    protected function cacheTags(): array
    {
        return ['repoist', $this->cacheKey()];
    }

    protected function cacheKey(string ...$parts): string
    {
        return implode('.', ['repoist', static::class, ...$parts]);
    }
}
