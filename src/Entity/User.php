<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Entity;

use DateTimeImmutable;
use DateTimeZone;

class User
{
    private int $id;
    private string $email;
    private string $hashedPassword;
    private DateTimeImmutable $createdAt;

    private function __construct() {}

    public static function from(
        int $id,
        string $email,
        string $pwd,
        string $created
    ): User
    {
        $user = new User();

        $user->id = $id;
        $user->email = $email;
        $user->hashedPassword = $pwd;
        $user->createdAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $created,
            new DateTimeZone('UTC')
        );

        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}