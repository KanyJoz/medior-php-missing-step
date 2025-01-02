<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Repository;

use KanyJoz\CodeFlash\Entity\Card;

interface CardRepositoryInterface
{
    function all(): array;
    function getByID(int $id): Card;
    function save(string $question, string $answer): int;
}