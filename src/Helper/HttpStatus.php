<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Helper;

enum HttpStatus: int
{
    // 200 Group
    case OK = 200;
    case Created = 201;

    // 300 Group
    case SeeOther = 303;

    // 400 Group
    case UnprocessableEntity = 422;
}