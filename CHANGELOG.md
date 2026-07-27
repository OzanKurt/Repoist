# Changelog

All notable changes to `ozankurt/repoist` are documented here. This project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]

### Added
- Laravel 12 and PHP 8.3 / 8.4 support.
- Full type declarations across the repository, contract and criteria classes.
- Automatic binding of `App\Repositories\Contracts\XRepository` contracts to their `EloquentXRepository` implementations (toggle with the `auto_bind` config key).
- New repository methods: `findMany`, `firstOrCreate`, `updateOrCreate`, `count`, `exists`, `pluck` and `chunk`.
- New built-in criteria: `OrderBy`, `Latest`, `WhereIn`, `Search` and `WithTrashed`.
- Publishable stubs via `vendor:publish --tag=repoist-stubs`; the generators prefer a published stub over the packaged one.
- `--force` option for `make:repository` and `make:criterion` to overwrite without prompting.
- Nested name support, e.g. `make:repository Blog/Post` and `make:criterion Blog/Published`.
- Configurable `models` namespace/path so generated repositories reference `App\Models\*`.
- `ByUser` criterion now accepts a custom column as its second argument.
- `EagerLoad` criterion now accepts a single relation string in addition to an array.
- Generated contracts now extend `RepositoryInterface`.
- Test suite (Pest + Orchestra Testbench), Pint, Larastan (level 8), Rector and a GitHub Actions pipeline.

### Changed
- Rebuilt on `spatie/laravel-package-tools`.
- Each operation now runs against a fresh query, so a reused repository instance no longer leaks `where` clauses or criteria between calls. Criteria queued with `withCriteria()` apply to the next terminal call only, then reset.
- Criteria always receive an `Illuminate\Database\Eloquent\Builder`.
- `find` / `findWhereFirst` use `findOrFail` / `firstOrFail`.
- `update` and `delete` now return `bool`.
- Stub files use the `.stub` extension and `{{ placeholder }}` syntax.
- Configuration moved from `src/config/repoist.php` to `config/repoist.php`.

### Removed
- `Kurt\Repoist\Exceptions\NoEntityDefined`; the `entity()` method is now abstract and enforced at author time.
- Support for Laravel 5.2 to 6 (use the `1.x` line for those).

## [1.x]

Legacy releases targeting Laravel 5.2 to 6. See the git history for details.
