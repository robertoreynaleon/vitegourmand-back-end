<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310150441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les FK qui bloquent la suppression du PRIMARY KEY composite
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85CCD7E912');
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85148EB0CB');
        // Ajouter l'id auto-increment, changer la PK
        $this->addSql('ALTER TABLE menu_dishes ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        // Contrainte unique sur (menu_id, dish_id)
        $this->addSql('CREATE UNIQUE INDEX unique_menu_dish ON menu_dishes (menu_id, dish_id)');
        // Recréer les FK
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85CCD7E912 FOREIGN KEY (menu_id) REFERENCES menus (id)');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85148EB0CB FOREIGN KEY (dish_id) REFERENCES dishes (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85CCD7E912');
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85148EB0CB');
        $this->addSql('DROP INDEX unique_menu_dish ON menu_dishes');
        $this->addSql('ALTER TABLE menu_dishes MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE menu_dishes DROP id, DROP PRIMARY KEY, ADD PRIMARY KEY (menu_id, dish_id)');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85CCD7E912 FOREIGN KEY (menu_id) REFERENCES menus (id)');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85148EB0CB FOREIGN KEY (dish_id) REFERENCES dishes (id)');
    }
}
