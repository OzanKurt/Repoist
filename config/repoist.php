<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | These namespaces are prefixed with the application's base namespace
    | (usually "App"). They determine where generated classes live and how
    | they reference each other.
    |
    */
    'namespaces' => [
        'models' => 'Models',
        'contracts' => 'Repositories\Contracts',
        'repositories' => 'Repositories\Eloquent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Paths are resolved relative to the application's "app" directory. They
    | must mirror the namespaces above so PSR-4 autoloading keeps working.
    |
    */
    'paths' => [
        'models' => 'Models',
        'contracts' => 'Repositories/Contracts',
        'repositories' => 'Repositories/Eloquent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-bind contracts to implementations
    |--------------------------------------------------------------------------
    |
    | When enabled, Repoist binds every "App\Repositories\Contracts\XRepository"
    | interface to its "App\Repositories\Eloquent\EloquentXRepository"
    | implementation automatically, so you can type-hint the contract without
    | writing a single line of container binding. Any binding you register
    | manually always takes precedence.
    |
    */
    'auto_bind' => true,

];
