<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Repository;

use KanyJoz\CodeFlash\Exception\Card\DatabaseException;
use KanyJoz\CodeFlash\Exception\Card\DuplicateEmailException;
use KanyJoz\CodeFlash\Exception\Card\InvalidCredentialsException;
use Override;
use PDO;
use Throwable;

class UserRepositoryMySQL implements UserRepositoryInterface
{
    // We inject the PDO, so we can execute SQL
    public function __construct(private PDO $pdo) {}

    /**
     * @throws DatabaseException
     * @throws DuplicateEmailException
     */
    #[Override]
    public function save(string $email, string $password): void
    {
        // We use the password_hash function with the DEFAULT algorithm, that is BCRYPT
        // And we give a 12 cost instead of the default 10
        // it means the amount of processing power to create a secure hash from the string
        // More cost more robust hash, but more time to create it, I recommend using 12
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        $sql = 'INSERT INTO users (email, hashed_password, created_at)
                VALUES(:email, :hashed_password, NOW())';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'email' => $email,
                'hashed_password' => $hashedPassword,
            ]);
        } catch (Throwable $ex) {
            // Here we triage on the Exception and check if the SQL failed
            // because of the UNIQUE constraint on the email column or because something else
            if (
                $ex->getCode() === '23000'
                && str_contains($ex->getMessage(), 'constraint_users_email')
            ) {
                throw new DuplicateEmailException('duplicate email');
            }

            throw new DatabaseException('user save() failed');
        }
    }

    /**
     * @throws DatabaseException
     * @throws InvalidCredentialsException
     */
    #[Override]
    public function check(string $email, string $password): int
    {
        $sql = 'SELECT id, hashed_password FROM users WHERE email = :email';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch();
            if ($user === false) {
                throw new InvalidCredentialsException('user not found');
            }
        } catch (InvalidCredentialsException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException('user check() failed', previous: $ex);
        }

        // And then when we got the hashed version of the password from the database
        // we just use the password_verify() function, easy
        if (!password_verify($password, $user['hashed_password'])) {
            throw new InvalidCredentialsException('password mismatch');
        }

        return $user['id'];
    }
}