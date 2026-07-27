<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;

interface RepositoryInterface
{
    /**
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function all(?int $paginate = null): Collection|LengthAwarePaginator;

    public function find(int|string $id): Model;

    /**
     * @param  array<int, int|string>  $ids
     * @return Collection<int, Model>
     */
    public function findMany(array $ids): Collection;

    /**
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function findWhere(string $column, mixed $value, ?int $paginate = null): Collection|LengthAwarePaginator;

    public function findWhereFirst(string $column, mixed $value): Model;

    /**
     * @param  array<int, string>|string  $columns
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function findWhereLike(array|string $columns, string $value, ?int $paginate = null): Collection|LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $properties
     */
    public function create(array $properties): Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function firstOrCreate(array $attributes, array $values = []): Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;

    /**
     * @param  array<string, mixed>  $properties
     */
    public function update(int|string $id, array $properties): bool;

    public function delete(int|string $id): bool;

    public function count(): int;

    public function exists(): bool;

    /**
     * @return BaseCollection<array-key, mixed>
     */
    public function pluck(string $column, ?string $key = null): BaseCollection;

    /**
     * @param  callable(Collection<int, Model>, int): mixed  $callback
     */
    public function chunk(int $count, callable $callback): bool;
}
