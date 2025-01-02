<?php

declare(strict_types=1);

use Dotenv\Dotenv;

return function (Dotenv $dotenv) {
    // APP
    $dotenv->required('APP_NAME');
    $dotenv->required('APP_ENV')->allowedValues(['development', 'production']);

    // Database
    $dotenv->required('DB_DRIVER');
    $dotenv->required('DB_HOST');
    $dotenv->required('DB_PORT');
    $dotenv->required('DB_NAME');
    $dotenv->required('DB_USER');
    $dotenv->required('DB_PASSWORD');
    $dotenv->required('DB_CHARSET');

    //...

    // Session
    $dotenv->required('SESSION_NAME');
    $dotenv->required('SESSION_CACHE_LIMITER');

    $dotenv->required('SESSION_COOKIE_LIFETIME')->isInteger();
    $dotenv->required('SESSION_COOKIE_SECURE')->isBoolean();
    $dotenv->required('SESSION_COOKIE_HTTPONLY')->isBoolean();
};