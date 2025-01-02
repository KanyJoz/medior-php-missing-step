<?php

declare(strict_types=1);

use KanyJoz\CodeFlash\Middleware\CommonHeadersMiddleware;
use KanyJoz\CodeFlash\Middleware\RequestLoggerMiddleware;
use KanyJoz\CodeFlash\Middleware\TwigGlobalsMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;

return function(App $app)
{
    $app->add(CommonHeadersMiddleware::class);
    $app->add(TwigGlobalsMiddleware::class);
    $app->add(RoutingMiddleware::class);
    $app->add(BodyParsingMiddleware::class);
    $app->add(RequestLoggerMiddleware::class);
    $app->add(Guard::class);
    $app->add(SessionStartMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
