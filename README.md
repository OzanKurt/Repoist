# Repoist

Laravel 5 repository generator.

## Usage

### Step 1: Install Through Composer

```
composer require ozankurt/repoist --dev
```

### Step 2: Add the Service Provider

You'll only want to use these generators for local development, so you don't want to update the production  `providers` array in `config/app.php`. Instead, add the provider in `app/Providers/AppServiceProvider.php`, like so:

```php
public function register()
{
	if ($this->app->isLocal()) {
		$this->app->register('Kurt\Repoist\RepoistServiceProvider');
	}
}
```

### Step 3: Publish and edit the configurations

Run `php artisan vendor:publish` from the console to configure the Repoist according to your needs. 

### Step 4: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands in the `make:repository` namespace section.

## Examples

- [Repositories Without Model](#repositories-without-schema)
- [Repositories With Model](#repositories-with-schema)

### Repositories Without Schema

```
php artisan make:repository Task
```

Will output:

- `app/Repositories/Task/TaskRepository.php` (contract)
- `app/Repositories/Task/EloquentTaskRepository.php`

### Repositories With Schema

```
php artisan make:repository Task -m
```

Will output:

- `app/Repositories/Task/TaskRepository.php` (contract)
- `app/Repositories/Task/EloquentTaskRepository.php`
- `app/Task.php`

## Configurations

If somehow you cannot publish the `config/repoist.php` from artisan here you can copy and use it.

```
<?php

return [

	/**
	 * Default path of repositories in `app` folder.
	 * In this case:
	 * 		app/Repositories
	 */
	'path' => 'Repositories',

	/**
	 * Default path of models in laravel is the `app` folder.
	 * In this case:
	 * 		app/
	 */
	'model_path' => '',

	/**
	 * Configure the naming convention you wish for your repositories.
	 */
	'fileNames' => [
		'contract' => '{name}Repository',
		'eloquent' => 'Eloquent{name}Repository',
	],

];
```
