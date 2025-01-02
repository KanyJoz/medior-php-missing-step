<?php

declare(strict_types=1);

use KanyJoz\CodeFlash\Controller\CardController;
use KanyJoz\CodeFlash\Controller\HomeController;
use KanyJoz\CodeFlash\Controller\UserController;
use KanyJoz\CodeFlash\Middleware\AuthenticationCheckMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function(App $app): void
{
    // restricted
    $app->group('', function(RouteCollectorProxy $group) {
        $group->get('/cards/create', [CardController::class, 'create']);
        $group->post('/cards/save', [CardController::class, 'save']);
        $group->post('/users/logout', [UserController::class, 'logoutPost']);
    })->add(AuthenticationCheckMiddleware::class);

    // home
    $app->get('/', HomeController::class);

    // card
    $app->get('/cards/{cardID}', [CardController::class, 'show']);

    // user
    $app->get('/users/register', [UserController::class, 'register']);
    $app->post('/users/register', [UserController::class, 'registerPost']);
    $app->get('/users/login', [UserController::class, 'login']);
    $app->post('/users/login', [UserController::class, 'loginPost']);
};
