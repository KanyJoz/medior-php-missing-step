<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Helper;

class PathBuilder
{
    public function page(string $filePath): string
    {
        return PAGES_PATH . '/' . $filePath;
    }

    public function card(string $filePath): string
    {
        return CARD_PATH . '/' . $filePath;
    }

    public function user(string $filePath): string
    {
        return USER_PATH . '/' . $filePath;
    }
}