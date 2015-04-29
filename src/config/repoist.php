<?php

return [

	/**
	 * The path to repositories folder.
	 *
	 * Default: app/Repositories/
	 */
	'path' => 'Repositories',

	/**
	 * The path to models.
	 *
	 * Default: app/
	 */
	'model_path' => '',

	/**
	 * Repository naming conventions.
	 *
	 * Example: php artisan make:repository Users 
	 * 		- Contract: UsersRepository
	 * 		- Eloquent: EloquentUsersRepository
	 */
	'fileNames' => [

		'contract' => '{name}Repository',

		'eloquent' => 'Eloquent{name}Repository',
		
	],

];