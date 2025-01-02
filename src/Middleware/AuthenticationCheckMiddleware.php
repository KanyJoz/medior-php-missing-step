<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Middleware;

use KanyJoz\CodeFlash\Helper\Auth;
use KanyJoz\CodeFlash\Helper\HttpStatus;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

readonly class AuthenticationCheckMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Auth $auth,
        private ResponseFactoryInterface $responseFactory
    ) {}

    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$this->auth->isLoggedIn()) {
            return $this->responseFactory
                ->createResponse(HttpStatus::SeeOther->value, 'See Other')
                ->withHeader('Location', '/users/login');
        }

        $request = $request
            ->withHeader('Cache-Control', 'no-store');

        return $handler->handle($request);
    }
}

// Users
// Preparations
// User Repository
// User Registration
// User Login
// User Logout
// Authentication Vs. Authorization
// CSRF
// Thank You