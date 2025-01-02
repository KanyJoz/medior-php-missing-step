<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241231195902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL,
                hashed_password VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL
            );
        ");

        $this->addSql("ALTER TABLE users ADD CONSTRAINT constraint_users_email UNIQUE (email);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS users;");
    }
}
