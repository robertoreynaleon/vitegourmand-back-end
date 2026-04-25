<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Création de la table `password_reset_tokens`.
 * Stocke les tokens de réinitialisation de mot de passe.
 * Chaque token est lié à un utilisateur, a une durée de validité d'une heure
 * et peut être marqué comme utilisé pour empêcher sa réutilisation.
 */
final class Version20260425000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table password_reset_tokens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE password_reset_tokens (
                id          INT AUTO_INCREMENT NOT NULL,
                user_id     INT NOT NULL,
                token       VARCHAR(64)  NOT NULL,
                expires_at  DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                used_at     DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                created_at  DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                UNIQUE INDEX UNIQ_TOKEN (token),
                INDEX IDX_USER (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE password_reset_tokens
            ADD CONSTRAINT FK_PRT_USER
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_tokens DROP FOREIGN KEY FK_PRT_USER');
        $this->addSql('DROP TABLE password_reset_tokens');
    }
}
