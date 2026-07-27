<?php

declare(strict_types=1);

namespace Kurt\Repoist\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Kurt\Repoist\Repositories\Contracts\RepositoryInterface;
use Kurt\Repoist\Repositories\Criteria\CriteriaInterface;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

abstract class AbstractRepository implements CriteriaInterface, RepositoryInterface
{
    protected Model $model;

    /**
     * Criteria queued for the next terminal operation.
     *
     * @var list<CriterionInterface>
     */
    protected array $criteria = [];

    public function __construct()
    {
        $this->model = $this->resolveModel();
    }

    /**
     * The fully qualified model class the repository wraps.
     *
     * @return class-string<Model>
     */
    abstract public function entity(): string;

    /**
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function all(?int $paginate = null): Collection|LengthAwarePaginator
    {
        return $this->paginateOrGet($this->newQuery(), $paginate);
    }

    public function find(int|string $id): Model
    {
        return $this->newQuery()->findOrFail($id);
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return Collection<int, Model>
     */
    public function findMany(array $ids): Collection
    {
        return $this->newQuery()->findMany($ids);
    }

    /**
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function findWhere(string $column, mixed $value, ?int $paginate = null): Collection|LengthAwarePaginator
    {
        return $this->paginateOrGet($this->newQuery()->where($column, $value), $paginate);
    }

    public function findWhereFirst(string $column, mixed $value): Model
    {
        return $this->newQuery()->where($column, $value)->firstOrFail();
    }

    /**
     * @param  array<int, string>|string  $columns
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    public function findWhereLike(array|string $columns, string $value, ?int $paginate = null): Collection|LengthAwarePaginator
    {
        $columns = (array) $columns;

        $query = $this->newQuery()->where(function (Builder $query) use ($columns, $value): void {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', $value);
            }
        });

        return $this->paginateOrGet($query, $paginate);
    }

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function create(array $properties): Model
    {
        $model = $this->model->newQuery()->create($properties);

        $this->afterCreate($model);

        return $model;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->newQuery()->firstOrCreate($attributes, $values);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->newQuery()->updateOrCreate($attributes, $values);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function update(int|string $id, array $properties): bool
    {
        $model = $this->find($id);

        $updated = (bool) $model->update($properties);

        if ($updated) {
            $this->afterUpdate($model);
        }

        return $updated;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);

        $deleted = (bool) $model->delete();

        if ($deleted) {
            $this->afterDelete($model);
        }

        return $deleted;
    }

    public function count(): int
    {
        return $this->newQuery()->count();
    }

    public function exists(): bool
    {
        return $this->newQuery()->exists();
    }

    /**
     * @return BaseCollection<array-key, mixed>
     */
    public function pluck(string $column, ?string $key = null): BaseCollection
    {
        return $this->newQuery()->pluck($column, $key);
    }

    /**
     * @param  callable(Collection<int, Model>, int): mixed  $callback
     */
    public function chunk(int $count, callable $callback): bool
    {
        return $this->newQuery()->chunk($count, $callback);
    }

    /**
     * Queue one or more criteria for the next terminal operation.
     *
     * @param  CriterionInterface|array<int, CriterionInterface>  ...$criteria
     */
    public function withCriteria(...$criteria): static
    {
        foreach (Arr::flatten($criteria) as $criterion) {
            if ($criterion instanceof CriterionInterface) {
                $this->criteria[] = $criterion;
            }
        }

        return $this;
    }

    /**
     * Resolve the model instance from the container.
     */
    protected function resolveModel(): Model
    {
        /** @var Model $model */
        $model = app($this->entity());

        return $model;
    }

    /**
     * Build a fresh query, applying (and then clearing) any queued criteria.
     *
     * @return Builder<Model>
     */
    protected function newQuery(): Builder
    {
        $query = $this->model->newQuery();

        foreach ($this->criteria as $criterion) {
            $query = $criterion->apply($query);
        }

        $this->criteria = [];

        return $query;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Collection<int, Model>|LengthAwarePaginator<int, Model>
     */
    protected function paginateOrGet(Builder $query, ?int $paginate): Collection|LengthAwarePaginator
    {
        return $paginate ? $query->paginate($paginate) : $query->get();
    }

    /**
     * Called after a record is created. Override to hook in side effects.
     */
    protected function afterCreate(Model $model): void {}

    /**
     * Called after a record is updated. Override to hook in side effects.
     */
    protected function afterUpdate(Model $model): void {}

    /**
     * Called after a record is deleted. Override to hook in side effects.
     */
    protected function afterDelete(Model $model): void {}
}
