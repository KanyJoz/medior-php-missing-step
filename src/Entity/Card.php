<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Entity;

use DateTimeImmutable;
use DateTimeZone;

class Card
{
    private int $id;
    private string $question;
    private string $answer;
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function from(int $id, string $question, string $answer, string $createdAt): Card
    {
        // Create a new Card object with uninitialized fields
        $card = new Card();

        // Set the data
        $card->id = $id;
        $card->question = $question;
        $card->answer = $answer;
        $card->createdAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $createdAt,
            new DateTimeZone('UTC')
        );

        // Return the Entity
        return $card;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}