<?php

declare(strict_types=1);

$app_env = $_ENV['APP_ENV'];

return [
    'app' => [
        'environment' => $app_env,
        'name' => $_ENV['APP_NAME'],
    ],
    'twig' => [
        'template' => [
            'path' => RESOURCES_PATH,
            'options' => [
                'cache' => TWIG_CACHE_PATH,
                'auto_reload' => $app_env === 'development',
            ],
        ],
    ],
    'errors' => [
        'display' => $app_env === 'development',
        'use_logger' => true,
        'log_details' => true,
    ],
    'db' => [
        'driver' => $_ENV['DB_DRIVER'],
        'mysql' => [
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'db_name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASSWORD'],
            'charset' => $_ENV['DB_CHARSET'],
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
    'session' => [
        'name' => $_ENV['SESSION_NAME'],
        'cache_limiter' => $_ENV['SESSION_CACHE_LIMITER'],
        'cookie' => [
            'lifetime' => intval($_ENV['SESSION_COOKIE_LIFETIME']),
            'path' => $_ENV['SESSION_COOKIE_PATH' ] ?? null,
            'domain' => $_ENV['SESSION_COOKIE_DOMAIN'] ?? null,
            'secure' => filter_var($_ENV['SESSION_COOKIE_SECURE'], FILTER_VALIDATE_BOOL),
            'httponly' => filter_var($_ENV['SESSION_COOKIE_HTTPONLY'], FILTER_VALIDATE_BOOL)
        ],
    ],
];
