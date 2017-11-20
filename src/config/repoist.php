<?php

return [

    /**
     * Namespaces are being prefixed with the applications base namespace.
     */
    'namespaces' => [
        'contracts'    => 'Repositories\Contracts',
        'repositories' => 'Repositories\Eloquent',
    ],

    /**
     * Paths will be used with the `app()->basePath()` function to reach app directory.
     */
    'paths' => [
        'contracts'    => 'Repositories/Contracts/',
        'repositories' => 'Repositories/Eloquent/',
    ],

];
