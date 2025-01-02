<?php

declare(strict_types=1);

namespace KanyJoz\CodeFlash\Repository;

use KanyJoz\CodeFlash\Entity\Card;
use KanyJoz\CodeFlash\Exception\Card\DatabaseException;
use KanyJoz\CodeFlash\Exception\Card\RecordNotFoundException;
use Override;
use PDO;
use Throwable;

readonly class CardRepositoryMySQL implements CardRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @throws DatabaseException
     */
    #[Override]
    public function all(): array
    {
        $sql = 'SELECT id, question, answer, created_at FROM cards
                ORDER BY id DESC
                LIMIT 10';

        $stmt = $this->pdo->query($sql);
        $cards = $stmt->fetchAll();

        if ($cards === false) {
            throw new DatabaseException('cards all() failed');
        }

        return array_map(function($card){
            return Card::from(
                $card['id'],
                $card['question'],
                $card['answer'],
                $card['created_at'],
            );
        }, $cards);
    }

    /**
     * @throws RecordNotFoundException
     * @throws DatabaseException
     */
    #[Override]
    public function getByID(int $id): Card
    {
        $sql = 'SELECT id, question, answer, created_at
                FROM cards
                WHERE id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            $card = $stmt->fetch();
            if ($card === false) {
                throw new RecordNotFoundException('card not found');
            }
        } catch (RecordNotFoundException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new DatabaseException('card getByID() failed', previous: $ex);
        }

        return Card::from(
            $card['id'],
            $card['question'],
            $card['answer'],
            $card['created_at'],
        );
    }

    /**
     * @throws DatabaseException
     */
    #[Override]
    public function save(string $question, string $answer): int
    {
        $sql = 'INSERT INTO cards (question, answer, created_at)
                VALUES(:question, :answer, NOW())';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'question' => $question,
                'answer' => $answer,
            ]);

            $id = $this->pdo->lastInsertId();

            $this->pdo->commit();
        } catch (Throwable $ex) {
            $this->pdo->rollBack();
            throw new DatabaseException('card save() failed', previous: $ex);
        }

        return intval($id);
    }
}

