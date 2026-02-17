<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value determines the default "server" that will be used by Octane
    | when starting, restarting, or stopping your application server.
    | You are free to change this to the supported server of your choice.
    |
    */

    'server' => env('OCTANE_SERVER', 'swoole'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this configuration value is set to "true", Octane will inform the
    | framework that all absolute URLs should be generated using the HTTPS
    | protocol. Otherwise your links may be generated using plain HTTP.
    |
    */

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    |
    | All of the event listeners for Octane's events are defined below. These
    | listeners are responsible for resetting your application's state after
    | each request. You may even add your own listeners to this list.
    |
    */

    'listeners' => [
        Laravel\Octane\Events\WorkerStarting::class => [
            Laravel\Octane\Listeners\EnsureUploadedFilesAreValid::class,
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
            Laravel\Octane\Listeners\CollectGarbage::class,
        ],

        Laravel\Octane\Events\WorkerStopping::class => [
            Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
        ],

        Laravel\Octane\Events\TaskReceived::class => [
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
            Laravel\Octane\Listeners\CollectGarbage::class,
        ],

        Laravel\Octane\Events\TaskTerminated::class => [
            Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
        ],

        Laravel\Octane\Events\TickReceived::class => [
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
            Laravel\Octane\Listeners\CollectGarbage::class,
        ],

        Laravel\Octane\Events\TickTerminated::class => [
            Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
        ],

        Laravel\Octane\Events\OperationTerminated::class => [
            Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
            Laravel\Octane\Listeners\DisconnectFromDatabases::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    |
    | The bindings listed below will be preloaded when the application boots
    | to reduce the number of container lookups performed during a request.
    |
    */

    'warm' => [
        'cache',
        'cache.store',
        'cache.psr6',
        'db',
        'db.connection',
        'db.connection.mysql',
        'files',
        'hash',
        'hash.driver',
        'log',
        'log.channel',
        'queue',
        'queue.connection',
        'queue.failer',
        'redis',
        'redis.connection',
        'session',
        'session.store',
        'translator',
        'url',
        'view',
        'view.engine.resolver',
        'view.finder',
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table
    |--------------------------------------------------------------------------
    |
    | While using Swoole, you may leverage the Octane cache, which is powered
    | by a Swoole table. You may set the maximum number of rows as well as
    | the number of bytes per row using the configuration options below.
    |
    */

    'cache' => [
        'rows' => 1000,
        'bytes' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Swoole Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the Swoole settings that will be used when
    | starting the Octane server. You should tweak these based on the
    | requirements of your application and the capabilities of your server.
    |
    */

    'swoole' => [
        'options' => [
            'log_file' => storage_path('logs/swoole_http.log'),
            'package_max_length' => 10 * 1024 * 1024,
            'buffer_output_size' => 32 * 1024 * 1024,
            'socket_buffer_size' => 128 * 1024 * 1024,
            'max_request' => 1000,
            'worker_num' => 4,
            'reactor_num' => 4,
            'task_worker_num' => 4,
            'max_conn' => 100000,
            'open_tcp_nodelay' => true,
            'tcp_fastopen' => true,
            'enable_coroutine' => true,
            'max_coroutine' => 100000,
            'enable_static_handler' => true,
            'document_root' => public_path(),
            'enable_compression' => true,
            'compression_min_length' => 20,
            'compression_types' => 'gzip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane FrankenPHP Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the FrankenPHP settings that will be used when
    | starting the Octane server. You should tweak these based on the
    | requirements of your application and the capabilities of your server.
    |
    */

    'frankenphp' => [
        'worker_count' => null,
        'max_requests' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane OpenSwoole Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the OpenSwoole settings that will be used when
    | starting the Octane server. You should tweak these based on the
    | requirements of your application and the capabilities of your server.
    |
    */

    'openswoole' => [
        'options' => [
            'log_file' => storage_path('logs/openswoole_http.log'),
            'package_max_length' => 10 * 1024 * 1024,
            'buffer_output_size' => 32 * 1024 * 1024,
            'socket_buffer_size' => 128 * 1024 * 1024,
            'max_request' => 1000,
            'worker_num' => 4,
            'reactor_num' => 4,
            'task_worker_num' => 4,
            'max_conn' => 100000,
            'open_tcp_nodelay' => true,
            'tcp_fastopen' => true,
            'enable_coroutine' => true,
            'max_coroutine' => 100000,
            'enable_static_handler' => true,
            'document_root' => public_path(),
            'enable_compression' => true,
            'compression_min_length' => 20,
            'compression_types' => 'gzip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane RoadRunner Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the RoadRunner settings that will be used when
    | starting the Octane server. You should tweak these based on the
    | requirements of your application and the capabilities of your server.
    |
    */

    'roadrunner' => [
        'rpc_port' => env('OCTANE_RPC_PORT', 6001),
        'max_execution_time' => 0,
    ],

];
