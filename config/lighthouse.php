<?php

return [
    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        'middleware' => [],
    ],
    'schema_path' => base_path('graphql/schema.graphql'),
    'cache' => [
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', false),
        'key' => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
        'store' => env('LIGHTHOUSE_CACHE_STORE'),
    ],
    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives'],
    ],
];
