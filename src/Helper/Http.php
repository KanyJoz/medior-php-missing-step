<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Helper;

use Psr\Http\Message\ResponseInterface as Response;

class Http
{
    // 200 Group
    public static function OK(
        Response $response,
        string $msg = "OK"
    ): Response
    {
        return $response->withStatus(HttpStatus::OK->value, $msg);
    }

    public static function Created(
        Response $response,
        string $msg = "Created"
    ): Response
    {
        return $response->withStatus(HttpStatus::Created->value, $msg);
    }

    // 300 Group
    public static function SeeOther(
        Response $response,
        string $msg = "SeeOther"
    ): Response
    {
        return $response->withStatus(HttpStatus::SeeOther->value, $msg);
    }

    // 400 Group
    public static function UnprocessableEntity(
        Response $response,
        string $msg = "Unprocessable Entity"
    ): Response
    {
        return $response->withStatus(
            HttpStatus::UnprocessableEntity->value,
            $msg
        );
    }
}