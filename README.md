# Repoist

Laravel 5.6 repository generator.

## Usage

### Step 1: Install Through Composer

```
composer require ozankurt/repoist
```

### Step 2: Publish and edit the configurations

**In Laravel:** Run `php artisan vendor:publish --tag=repoist-config` from the console to configure the Repoist according to your needs.

### Step 3: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands.

### For Lumen

In `bootstrap\app.php` enable Facades and Eloquent, also enable the configuration file.

```
$app->withFacades();
$app->withEloquent();

$app->configure('repoist');
```

In the Register service providers section add:

```
$app->register(Kurt\Repoist\RepoistServiceProvider::class);
```

## Examples

- [Repository](#repository)
- [Criterion](#criterion)

### Repository

```
php artisan make:repository Task
```

Will output:

- `app/Contracts/Task/TaskRepository.php` (contract)
- `app/Repositories/Eloquent/EloquentTaskRepository.php`
- `app/Task.php` (if needed)

### Criterion

```
php artisan make:criterion Completed
```

Will output:

- `app/Repositories/Eloquent/Criteria/Completed.php`

## Configurations

If somehow you cannot publish the `config/repoist.php` from artisan here you can copy and use it.

```php
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
	 * Paths will be used with the `app()->basePath().'/app/'` function to reach app directory.
	 */
	'paths' => [
	    'contracts' => 'Repositories/Contracts/',
	    'repositories' => 'Repositories/Eloquent/',
	],

];
```

## Configurations

Default methods of the `Kurt\Repoist\Repositories\Eloquent\AbstractRepository`.

| Method                | Usage
| --------------------- | ----------------------------------------------------------
| **all**               | $repo->all()
| **find**            	| $repo->find($id);
| **findWhere**         | $repo->findWhere($column, $value);
| **findWhereFirst**    | $repo->findWhereFirst($column, $value);
| **findWhereLike**     | $repo->findWhereLike($column, $value, $paginate = 0);
| **paginate**          | $repo->paginate($perPage = 10);
| **create**            | $repo->create(array $properties);
| **update**            | $repo->update($id, array $properties);
| **delete**            | $repo->delete($id);

## Example Usage

Customer.php
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * Customer has many Tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
    	return $this->hasMany(Ticket::class, 'customer_id', 'id');
    }
}
```
EloquentCustomerRepository.php
```php
<?php
namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepository;
use Kurt\Repoist\Repositories\Eloquent\AbstractRepository;

class EloquentCustomerRepository extends AbstractRepository implements CustomerRepository
{
    public function entity()
    {
        return Customer::class;
    }
}
```
PagesController.php
```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\CustomerRepository;
use Kurt\Repoist\Repositories\Eloquent\Criteria\EagerLoad;

class PagesController extends Controller
{
	private $customerRepository;

	function __construct(CustomerRepository $customerRepository)
	{
		$this->customerRepository = $customerRepository;
	}

    public function getHome()
    {
        $customersWithTickets = $this->customerRepository->withCriteria([
        	new EagerLoad(['tickets']),
        ])->all();

        return $customersWithTickets;
    }
}
```
