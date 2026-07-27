# Repoist

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ozankurt/repoist.svg)](https://packagist.org/packages/ozankurt/repoist)
[![Tests](https://github.com/OzanKurt/Repoist/actions/workflows/tests.yml/badge.svg)](https://github.com/OzanKurt/Repoist/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ozankurt/repoist.svg)](https://packagist.org/packages/ozankurt/repoist)
[![License](https://img.shields.io/packagist/l/ozankurt/repoist.svg)](LICENSE)

Repoist scaffolds the repository pattern for Laravel. It generates repository contracts, their Eloquent implementations and reusable query criteria, and ships a fully typed `AbstractRepository` base class with the CRUD methods you reach for every day.

## Requirements

- PHP 8.3+
- Laravel 12

> Using an older stack? Repoist `^1.0` supports Laravel 5.2 to 6. This `^2.0` line targets Laravel 12.

## Installation

```bash
composer require ozankurt/repoist
```

The service provider is registered automatically through package discovery. Publish the config file if you want to change the default namespaces, paths, or auto-binding:

```bash
php artisan vendor:publish --tag=repoist-config
```

## Generating a repository

```bash
php artisan make:repository Task
```

This creates:

- `app/Repositories/Contracts/TaskRepository.php`, the contract (extends `RepositoryInterface`)
- `app/Repositories/Eloquent/EloquentTaskRepository.php`, the Eloquent implementation
- `app/Models/Task.php`, the model, if it does not exist yet (you are asked first)

Nested names are supported and resolve against your `App\Models` namespace:

```bash
php artisan make:repository Blog/Post
```

Pass `--force` to overwrite existing files without prompting.

### Binding contracts to implementations

Repoist binds every `App\Repositories\Contracts\XRepository` contract to its `App\Repositories\Eloquent\EloquentXRepository` implementation automatically, so you can type-hint the contract with zero configuration:

```php
use App\Repositories\Contracts\TaskRepository;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepository $tasks) {}

    public function index()
    {
        return $this->tasks->all();
    }
}
```

Any binding you register yourself always takes precedence. To turn auto-binding off, publish the config and set `'auto_bind' => false`, then bind manually, for example in a dedicated `RepositoryServiceProvider`:

```php
namespace App\Providers;

use App\Repositories\Contracts\TaskRepository;
use App\Repositories\Eloquent\EloquentTaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        TaskRepository::class => EloquentTaskRepository::class,
    ];
}
```

## Available repository methods

| Method | Usage | Returns |
| --- | --- | --- |
| `all` | `$repo->all($paginate = null)` | `Collection` or `LengthAwarePaginator` |
| `find` | `$repo->find($id)` | `Model` (throws if missing) |
| `findMany` | `$repo->findMany(array $ids)` | `Collection` |
| `findWhere` | `$repo->findWhere($column, $value, $paginate = null)` | `Collection` or `LengthAwarePaginator` |
| `findWhereFirst` | `$repo->findWhereFirst($column, $value)` | `Model` (throws if missing) |
| `findWhereLike` | `$repo->findWhereLike($columns, $value, $paginate = null)` | `Collection` or `LengthAwarePaginator` |
| `paginate` | `$repo->paginate($perPage = 10)` | `LengthAwarePaginator` |
| `create` | `$repo->create(array $properties)` | `Model` |
| `firstOrCreate` | `$repo->firstOrCreate(array $attributes, array $values = [])` | `Model` |
| `updateOrCreate` | `$repo->updateOrCreate(array $attributes, array $values = [])` | `Model` |
| `update` | `$repo->update($id, array $properties)` | `bool` |
| `delete` | `$repo->delete($id)` | `bool` |
| `count` | `$repo->count()` | `int` |
| `exists` | `$repo->exists()` | `bool` |
| `pluck` | `$repo->pluck($column, $key = null)` | `Illuminate\Support\Collection` |
| `chunk` | `$repo->chunk($count, $callback)` | `bool` |

`find` and `findWhereFirst` throw `Illuminate\Database\Eloquent\ModelNotFoundException` when no record matches.

Each call runs against a fresh query, so a repository instance is safe to reuse (including when bound as a singleton): one call never leaks its `where` clauses or criteria into the next.

## Criteria

Criteria are small, reusable classes that shape a repository's query. Apply them with `withCriteria()`:

```php
use App\Repositories\Contracts\CustomerRepository;
use Kurt\Repoist\Repositories\Eloquent\Criteria\ByUser;
use Kurt\Repoist\Repositories\Eloquent\Criteria\EagerLoad;

$customers = $this->customers
    ->withCriteria([
        new ByUser($request->user()->id),
        new EagerLoad(['tickets']),
    ])
    ->all();
```

`withCriteria()` accepts either a variadic list or an array of criteria. Queued criteria apply to the next terminal call only, then reset.

### Built-in criteria

- `ByUser($userId, $column = 'user_id')`, constrain records to a user.
- `EagerLoad($relations)`, eager load one relation (string) or many (array).
- `OrderBy($column, $direction = 'asc')`, order the results.
- `Latest($column = 'created_at')`, order newest first.
- `WhereIn($column, array $values)`, constrain a column to a set of values.
- `Search($columns, $term)`, `LIKE` search across one or more columns.
- `WithTrashed()`, include soft-deleted records.

### Generating a criterion

```bash
php artisan make:criterion Completed
```

Creates `app/Repositories/Eloquent/Criteria/Completed.php`:

```php
namespace App\Repositories\Eloquent\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kurt\Repoist\Repositories\Criteria\CriterionInterface;

class Completed implements CriterionInterface
{
    public function __construct(protected mixed $value = null) {}

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('field', $this->value);
    }
}
```

Nested criteria (`make:criterion Blog/Published`) are supported too.

## Configuration

The published `config/repoist.php` controls the namespaces and paths used by the generators, plus auto-binding:

```php
return [
    'namespaces' => [
        'models'       => 'Models',
        'contracts'    => 'Repositories\Contracts',
        'repositories' => 'Repositories\Eloquent',
    ],

    'paths' => [
        'models'       => 'Models',
        'contracts'    => 'Repositories/Contracts',
        'repositories' => 'Repositories/Eloquent',
    ],

    'auto_bind' => true,
];
```

## Customizing the generated code

Publish the stubs to tailor what the generators produce:

```bash
php artisan vendor:publish --tag=repoist-stubs
```

The stubs land in `stubs/repoist`. Repoist prefers a published stub when present and falls back to the packaged one.

## Testing

```bash
composer test        # Pest
composer lint        # Pint (dry run)
composer stan        # PHPStan / Larastan (level 8)
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md). Upgrading from `1.x`? Read [UPGRADE-2.0.md](UPGRADE-2.0.md).

## Security

Please review [our security policy](SECURITY.md) for reporting vulnerabilities.

## License

Repoist is open-sourced software licensed under the [MIT license](LICENSE).
