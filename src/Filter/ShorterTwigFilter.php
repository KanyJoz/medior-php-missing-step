<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Filter;

class ShorterTwigFilter
{
    public static function shorter(string $text, int $wordCount): string
    {
        $words = explode(" ", $text);
        return implode(" ", array_slice($words, 0, $wordCount)) . '...';
    }
}