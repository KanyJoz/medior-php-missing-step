<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Middleware;

use KanyJoz\CodeFlash\Helper\Auth;
use Odan\Session\SessionInterface;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

readonly class TwigGlobalsMiddleware implements MiddlewareInterface
{
    // We inject the Guard
    // IMPORTANT it needs to run on the Request before this middleware runs
    public function __construct(
        private Twig $twig,
        private SessionInterface $session,
        private Auth $auth,
        private Guard $csrf
    ) {}

    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->twig->getEnvironment()->addGlobal('luckyNumber',
            rand(1, 10));
        $this->twig->getEnvironment()->addGlobal('flash',
            $this->session->getFlash());
        $this->twig->getEnvironment()->addGlobal('isLoggedIn',
            $this->auth->isLoggedIn());

        // We get the CSRF token name and value
        // And the CSRF token (itself) name and value from the Guard middleware
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();

        // This is an optional step, but it is better to pass
        // only one variable down as global variable to the Twig templates
        $csrfArray = [
            'keys' => [
                'name'  => $csrfNameKey,
                'value' => $csrfValueKey
            ],
            'name' => $csrfName,
            'value' => $csrfValue
        ];

        $this->twig->getEnvironment()->addGlobal('csrf', $csrfArray);

        return $handler->handle($request);
    }
}

