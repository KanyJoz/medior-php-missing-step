<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Validation;

final class Validator
{
    // Patterns
    public const string EMAIL_PATTERN =
        '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i';

    // ...
    private array $errors = [];
    private array $old = [];
    private array $generalErrors = [];

    public function valid(): bool
    {
        return count($this->errors) === 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function old(): array
    {
        return $this->old;
    }

    public function generalErrors(): array
    {
        return $this->generalErrors;
    }

    public function addError(string $key, string $msg): void
    {
        if (!array_key_exists($key, $this->errors)) {
            $this->errors[$key] = $msg;
        }
    }

    public function keep(string $key, mixed $value): void
    {
        if (!array_key_exists($key, $this->old)) {
            $this->old[$key] = $value;
        }
    }

    public function keepArray(array $array): void
    {
        $this->old = array_merge($array, $this->old);
    }

    public function addGeneralError(string $msg): void
    {
        $this->generalErrors[] = $msg;
    }

    // ...

    public function check(bool $ok, string $key, string $msg): void
    {
        if (!$ok) {
            $this->addError($key, $msg);
        }
    }

    // Pure validators, return bool
    public function matches(string $value, string $pattern): bool
    {
        return (bool)preg_match($pattern, $value);
    }

    public function maxChars(string $value, int $n): bool
    {
        return strlen($value) <= $n;
    }

    public function minChars(string $value, int $n): bool
    {
        return strlen($value) >= $n;
    }

    public function eq(int $number, int $to): bool
    {
        return $number === $to;
    }

    public function ne(int $number, int $to): bool
    {
        return $number !== $to;
    }

    public function ge(int $number, int $than): bool
    {
        return $number >= $than;
    }

    public function gt(int $number, int $than): bool
    {
        return $number > $than;
    }

    public function le(int $number, int $than): bool
    {
        return $number <= $than;
    }

    public function lt(int $number, int $than): bool
    {
        return $number < $than;
    }

    public function between(int $number, int $ge, int $le): bool
    {
        return $this->ge($number, $ge) && $this->le($number, $le);
    }

    public function permitted(mixed $value, array $in): bool
    {
        return in_array($value, $in);
    }
}
