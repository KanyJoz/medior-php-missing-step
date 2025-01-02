<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Validation;

class ValidatorFactory
{
    public function instance(): Validator
    {
        return new Validator();
    }
}