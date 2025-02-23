<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Lelkibeke Restaurant API',
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
        ],
    ],
    'paths' => [
        'docs' => storage_path('api-docs'),
        'views' => resource_path('views/vendor/l5-swagger'),
        'base' => env('L5_SWAGGER_BASE_PATH', null),
        'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
        'excludes' => [],
    ],
    'scanOptions' => [
        'analyzers' => [
            'phpDocumentor' => null,
        ],
        'paths' => [
            base_path('app'),
        ],
    ],
];
