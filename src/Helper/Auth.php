<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Helper;

use Odan\Session\SessionInterface;

readonly class Auth
{
    public function __construct(private SessionInterface $session) {}

    public function isLoggedIn(): bool
    {
        return $this->session->has('userID');
    }
}