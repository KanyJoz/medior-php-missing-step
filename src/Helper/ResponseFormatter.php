<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Helper;

use Psr\Http\Message\ResponseInterface;

class ResponseFormatter
{
    public function redirect(ResponseInterface $response, string $url): ResponseInterface
    {
        return Http::SeeOther($response)->withHeader('Location', $url);
    }
}
