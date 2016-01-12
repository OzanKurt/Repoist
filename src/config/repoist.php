<?php

return [

	/**
	 * Default path of repositories.
	 * In this case:
	 * 		app/Repositories
	 */
	'paths' => [
            'contract' => 'app/Interfaces',
            'eloquent' => 'app/Repositories',
            'model' => 'app/Models',
    ],
	/**
	 * Configure the naming convention you wish for your repositories.
	 *
	 * Example: php artisan make:repository Users
	 * 		- Contract: UsersRepository
	 * 		- Eloquent: EloquentUsersRepository
	 */
	'fileNames' => [

		'contract' => '{name}RepositoryInterface',

		'eloquent' => '{name}RepositoryEloquent',

        'model' => '{name}Eloquent',

	],

];
