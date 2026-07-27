# Repoist v2.0 — Design

Date: 2026-07-27

## Goal

Modernize `ozankurt/repoist` for **Laravel 12 / PHP 8.3+**, align it with the
KurtModules house style (Spatie package-tools, Pest 3, Pint, Larastan, Rector,
strict types, `final` classes), fix latent correctness bugs, and add the
highest-value features a repository generator should ship with.

## Scope (approved v2.0 cut)

1. Query isolation (correctness)
2. Built-in criteria library
3. Fuller base repository API
4. Auto-binding of contracts to implementations
5. Publishable stubs

Deferred to a later release: caching decorator, model events/hooks, `--all`
generation.

---

## 1. Query isolation (correctness)

**Problem.** `AbstractRepository` builds one `Builder` in the constructor and
mutates it. `findWhere`, `findWhereLike` and `withCriteria` permanently alter
that shared builder, so a reused instance (bound as a singleton, or two calls in
one request) bleeds state across calls.

**Fix.** The repository holds the resolved **Model** plus a list of **pending
criteria**. Every terminal operation builds a fresh query:

```php
protected function newQuery(): Builder
{
    $query = $this->model->newQuery();

    foreach ($this->criteria as $criterion) {
        $query = $criterion->apply($query);
    }

    $this->criteria = []; // criteria are one-shot per terminal operation

    return $query;
}
```

- Reads (`all`, `find`, `findWhere*`, `paginate`, `count`, `exists`, `pluck`,
  `chunk`) start from `newQuery()`.
- `withCriteria(...)` only records criteria and returns `$this` (fluent).
- `create` uses a criteria-free `$this->model->newQuery()` so filters never leak
  into inserts.
- `update`/`delete` resolve through `find()`, so scoping criteria (e.g. tenant
  `ByUser`) still constrain the target row — an intentional, documented behavior.

## 2. Built-in criteria library

`src/Repositories/Eloquent/Criteria/` ships:

| Criterion | Behavior |
|-----------|----------|
| `EagerLoad(array $relations)` | `->with($relations)` (existing) |
| `ByUser($userId, string $column = 'user_id')` | `->where($column, $userId)` (existing, column now configurable) |
| `OrderBy(string $column, string $direction = 'asc')` | `->orderBy(...)` |
| `Latest(string $column = 'created_at')` | `->latest($column)` |
| `WhereIn(string $column, array $values)` | `->whereIn(...)` |
| `Search(array\|string $columns, string $term)` | grouped `orWhere LIKE %term%` |
| `WithTrashed()` | `->withTrashed()` (SoftDeletes models) |

All implement `CriterionInterface::apply(Builder $query): Builder`.

## 3. Fuller base repository API

`RepositoryInterface` + `AbstractRepository` (typed, criteria-aware):

- Existing: `all(?int $paginate = null)`, `find`, `findWhere`, `findWhereFirst`,
  `findWhereLike`, `paginate`, `create`, `update`, `delete`.
- Added: `firstOrCreate(array $attributes, array $values = [])`,
  `updateOrCreate(array $attributes, array $values = [])`,
  `findMany(array $ids)`, `count()`, `exists()`,
  `pluck(string $column, ?string $key = null)`,
  `chunk(int $count, callable $callback)`.
- `withCriteria(CriterionInterface|array ...$criteria): static` (in
  `CriteriaInterface`).

Return types use `Illuminate\Database\Eloquent\{Model, Collection}` and
`Illuminate\Contracts\Pagination\LengthAwarePaginator`.

## 4. Auto-binding

Config flag `auto_bind` (default `true`). At `packageBooted()`, a
`RepositoryBinder` scans the configured contracts directory; for each interface
`App\Repositories\Contracts\XRepository` it binds the implementation
`App\Repositories\Eloquent\EloquentXRepository` when that class exists. Bindings
are lazy closures, so there is no runtime cost until a repository is resolved.
Manual bindings in the app still win (only bind when not already bound).

## 5. Publishable stubs

`vendor:publish --tag=repoist-stubs` copies package stubs to
`base_path('stubs/repoist')`. `RepoistCommand::getStub()` prefers a published
stub when present and falls back to the packaged one, so teams can customize
generated code.

## Generator behavior (Laravel 12)

- Models resolve to `App\Models\*` (config `namespaces.models` / `paths.models`).
- `make:repository Blog/Post` → `App\Models\Blog\Post`, generates
  `PostRepository` contract + `EloquentPostRepository`, offers to create the
  model via `make:model` when missing.
- `make:criterion Blog/Published` → nested criterion under `Criteria\Blog`.
- Both commands support `--force`, create directories as needed, and confirm
  before overwriting otherwise.

## Tooling & repo health

`pint.json`, `phpstan.neon` (level 8 + larastan), `rector.php`,
`phpunit.xml.dist`, `.gitattributes`, `.gitignore`, `.editorconfig`,
`.github/workflows/tests.yml`, `.github/dependabot.yml`, `CHANGELOG.md`,
`UPGRADE-2.0.md`, rewritten `README.md`. Keep `SECURITY.md`, `LICENSE`.

## Tests (Pest 3 + Orchestra Testbench 10)

- Unit: `AbstractRepository` against in-memory SQLite with a stub model — CRUD,
  query isolation (no state bleed across calls), each new method, and every
  built-in criterion.
- Feature: `make:repository` and `make:criterion` generate expected files/content
  (including nested namespaces and `--force`); auto-binding resolves a contract
  to its implementation.

## Breaking changes (→ UPGRADE-2.0.md)

- Requires PHP 8.3+, Laravel 12+.
- `entity()` is now `abstract public function entity(): string;` — repositories
  must declare the `: string` return type.
- Criteria `apply()` consistently receives an Eloquent `Builder` and returns one.
- Query state no longer persists between calls; criteria apply to the next
  terminal operation only.
- `Kurt\Repoist\Exceptions\NoEntityDefined` removed (entity is enforced by the
  abstract signature).
- Config adds `namespaces.models`, `paths.models`, and `auto_bind`.
