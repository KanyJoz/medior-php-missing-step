<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Repository;

interface UserRepositoryInterface
{
    public function save(string $email, string $password): void;
    public function check(string $email, string $password): int;
}