# Repoist

Laravel 5.5 repository generator.

## Usage

### Step 1: Install Through Composer

```
composer require ozankurt/repoist
```

### Step 2: Register the Service Provider

Add the service provider to `config/app.php`.

```php
	/*
	 * Package Service Providers...
	 */
	Kurt\Repoist\RepoistServiceProvider::class,
```

### Step 3: Publish and edit the configurations

**In Laravel:** Run `php artisan vendor:publish --tag=repoist-config` from the console to configure the Repoist according to your needs.

### Step 4: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands.

## Examples

- [Repositories Without Model](#repositories-without-schema)
- [Repositories With Model](#repositories-with-schema)

### Repository

```
php artisan make:repository Task
```

Will output:

- `app/Contracts/Task/TaskRepository.php` (contract)
- `app/Repositories/Eloquent/EloquentTaskRepository.php`
- `app/Task.php` (if needed)

### Repositories With Schema (Laravel Only)

```
php artisan make:criterion Completed
```

Will output:

- `app/Repositories/Eloquent/Criteria/Completed.php`

## Configurations

If somehow you cannot publish the `config/repoist.php` from artisan here you can copy and use it.

```
<?php

return [

	/**
	 * Namespaces are being prefixed with the applications base namespace.
	 */
	'namespaces' => [
	    'contracts' => 'Repositories\Contracts',
	    'repositories' => 'Repositories\Eloquent',
	],

	/**
	 * Paths will be used with the `app_path()` function to reach app directory.
	 */
	'paths' => [
	    'contracts' => 'Repositories/Contracts/',
	    'repositories' => 'Repositories/Eloquent/',
	],

];
```
