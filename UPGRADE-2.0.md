# Upgrading from 1.x to 2.0

Repoist 2.0 targets **Laravel 12** and **PHP 8.3+**. The public surface is largely the same, but there are a few breaking changes to be aware of.

## Environment

- PHP must be 8.3 or higher.
- Laravel must be 12. If you are on Laravel 5.2 to 6, stay on `ozankurt/repoist:^1.0`.

## `entity()` is now abstract and typed

`AbstractRepository::entity()` is now declared as:

```php
abstract public function entity(): string;
```

Update your existing repositories to add the `string` return type:

```php
// Before
public function entity()
{
    return Task::class;
}

// After
public function entity(): string
{
    return Task::class;
}
```

Because `entity()` is enforced by the abstract method, the `Kurt\Repoist\Exceptions\NoEntityDefined` exception has been removed.

## Criteria receive a `Builder`

Criteria previously received either a model or a builder. They now always receive an `Illuminate\Database\Eloquent\Builder`. If you type-hinted the parameter, update it:

```php
use Illuminate\Database\Eloquent\Builder;

public function apply(Builder $query): Builder
{
    return $query->where('completed', true);
}
```

The `CriterionInterface::apply()` signature is now `apply(Builder $query): Builder`.

## Query state no longer persists between calls

In 1.x a repository mutated a single shared query builder, so filters and criteria leaked across calls on a reused instance. In 2.0 every operation runs against a fresh query, and criteria queued with `withCriteria()` apply to the next terminal call only, then reset. If any code relied on the old accumulating behavior, apply the criteria immediately before each call instead.

## Auto-binding

Contracts are now bound to their `Eloquent*` implementations automatically. If you previously registered these bindings by hand, you can remove them (yours still take precedence if you keep them). To opt out entirely, publish the config and set `'auto_bind' => false`.

## Return types

- `update()` and `delete()` now return `bool`.
- `find()` and `findWhereFirst()` throw `ModelNotFoundException` (via `findOrFail` / `firstOrFail`) instead of a manually constructed exception. The exception class is unchanged.

## Models namespace

Generated repositories now reference `App\Models\*` by default (Laravel 8+ layout). If your models live elsewhere, publish the config and adjust the `models` namespace/path:

```bash
php artisan vendor:publish --tag=repoist-config
```

## Stubs

Custom stubs now use the `.stub` extension and `{{ placeholder }}` syntax. If you published or overrode stubs in 1.x, re-create them from the new versions with `vendor:publish --tag=repoist-stubs`.
