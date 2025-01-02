<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241230230910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates cards table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS cards (
                id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
                question VARCHAR(255) NOT NULL,
                answer TEXT NOT NULL,
                created_at DATETIME NOT NULL
            );
        ");

        $this->addSql("CREATE INDEX idx_cards_created_at ON cards(created_at);");

        $this->addSql("
            INSERT INTO cards (question, answer, created_at) VALUES (
                'What is the difference between composer.json and composer.lock?',
                'The composer.lock file also stores the exact commit hash.',
                NOW()
            );
        ");

        $this->addSql("
            INSERT INTO cards (question, answer, created_at) VALUES (
                'What did PHP originally stand for?',
                'Personal Home Page.',
                NOW()
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS cards;");
    }
}
