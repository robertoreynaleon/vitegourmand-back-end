<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Correction de la migration Version20260306160431 pour MySQL 8.x.
 *
 * Problème : En MySQL 8.x, quand on DROP FOREIGN KEY, l'index implicite
 * associé est également supprimé automatiquement. Doctrine génère ensuite un
 * RENAME INDEX sur l'ancien nom de colonne (ex: "allergen_id") qui n'existe
 * plus. Il faut renommer l'index créé par MySQL sous le nom de la contrainte
 * FK (ex: "FK_BD8EDBE56E775A4A") vers le nom Doctrine attendu ("IDX_...").
 *
 * Tables concernées : dish_allergens, menu_dishes, menu_images, menus, users.
 * Tables OK sans modification : order_menus, orders, regimes, roles
 * (pas de DROP FK dans la migration initiale, les index implicites existent encore).
 */
final class Version20260306200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Finalise la migration initiale : corrige les RENAME INDEX cassés par le comportement MySQL 8.x (DROP FK supprime aussi l\'index implicite).';
    }

    public function up(Schema $schema): void
    {
        // ── dish_allergens ────────────────────────────────────────────────────
        // Étapes 1-7 déjà exécutées. MySQL a créé l'index "FK_BD8EDBE56E775A4A"
        // (l'index implicite "allergen_id" ayant été supprimé avec l'ancienne FK).
        $this->addSql('ALTER TABLE dish_allergens RENAME INDEX FK_BD8EDBE56E775A4A TO IDX_BD8EDBE56E775A4A');

        // ── menu_dishes ───────────────────────────────────────────────────────
        // DROP FK → supprime l'index implicite "dish_id"
        // ADD CONSTRAINT FK_8B0A8B85148EB0CB crée un index "FK_8B0A8B85148EB0CB"
        // → on renomme FK_... en IDX_...
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY `menu_dishes_ibfk_1`');
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY `menu_dishes_ibfk_2`');
        $this->addSql('ALTER TABLE menu_dishes CHANGE dish_type dish_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85CCD7E912 FOREIGN KEY (menu_id) REFERENCES menus (id)');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT FK_8B0A8B85148EB0CB FOREIGN KEY (dish_id) REFERENCES dishes (id)');
        $this->addSql('ALTER TABLE menu_dishes RENAME INDEX FK_8B0A8B85148EB0CB TO IDX_8B0A8B85148EB0CB');

        // ── menu_images ───────────────────────────────────────────────────────
        // DROP FK → supprime l'index implicite "menu_id"
        // ADD CONSTRAINT FK_3358E6B2CCD7E912 crée un index "FK_3358E6B2CCD7E912"
        $this->addSql('ALTER TABLE menu_images DROP FOREIGN KEY `menu_images_ibfk_1`');
        $this->addSql('ALTER TABLE menu_images ADD CONSTRAINT FK_3358E6B2CCD7E912 FOREIGN KEY (menu_id) REFERENCES menus (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_images RENAME INDEX FK_3358E6B2CCD7E912 TO IDX_3358E6B2CCD7E912');

        // ── menus ─────────────────────────────────────────────────────────────
        // DROP FK → supprime l'index implicite "regime_id"
        // ADD CONSTRAINT FK_727508CF35E7D534 crée un index "FK_727508CF35E7D534"
        $this->addSql('ALTER TABLE menus DROP FOREIGN KEY `menus_ibfk_1`');
        $this->addSql('ALTER TABLE menus CHANGE description description LONGTEXT DEFAULT NULL, CHANGE min_people min_people INT NOT NULL, CHANGE remaining_quantity remaining_quantity INT DEFAULT NULL, CHANGE advance_order_days advance_order_days INT NOT NULL');
        $this->addSql('ALTER TABLE menus ADD CONSTRAINT FK_727508CF35E7D534 FOREIGN KEY (regime_id) REFERENCES regimes (id)');
        $this->addSql('ALTER TABLE menus RENAME INDEX uq_menu_title TO UNIQ_727508CF2B36786B');
        $this->addSql('ALTER TABLE menus RENAME INDEX FK_727508CF35E7D534 TO IDX_727508CF35E7D534');

        // ── order_menus ───────────────────────────────────────────────────────
        // Aucun DROP FK → les index implicites "order_id" et "menu_id" existent encore
        $this->addSql('ALTER TABLE order_menus RENAME INDEX order_id TO IDX_F1EB7C328D9F6D38');
        $this->addSql('ALTER TABLE order_menus RENAME INDEX menu_id TO IDX_F1EB7C32CCD7E912');

        // ── orders ────────────────────────────────────────────────────────────
        // Aucun DROP FK → l'index implicite "user_id" existe encore
        $this->addSql('ALTER TABLE orders CHANGE order_date order_date DATETIME NOT NULL, CHANGE delivery_address delivery_address LONGTEXT NOT NULL, CHANGE equipment_loan equipment_loan TINYINT(1) NOT NULL, CHANGE equipment_returned equipment_returned TINYINT(1) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE orders RENAME INDEX user_id TO IDX_E52FFDEEA76ED395');

        // ── regimes ───────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE regimes RENAME INDEX uq_regime_label TO UNIQ_42177456EA750E8');

        // ── roles ─────────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE roles RENAME INDEX uq_role_name TO UNIQ_B63E2EC75E237E06');

        // ── users ─────────────────────────────────────────────────────────────
        // DROP FK → supprime l'index implicite "role_id"
        // ADD CONSTRAINT FK_1483A5E9D60322AC crée un index "FK_1483A5E9D60322AC"
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY `users_ibfk_1`');
        $this->addSql('ALTER TABLE users CHANGE role_id role_id INT NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
        $this->addSql('ALTER TABLE users RENAME INDEX uq_email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE users RENAME INDEX FK_1483A5E9D60322AC TO IDX_1483A5E9D60322AC');
    }

    public function down(Schema $schema): void
    {
        // ── dish_allergens ────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE dish_allergens RENAME INDEX IDX_BD8EDBE56E775A4A TO FK_BD8EDBE56E775A4A');

        // ── menu_dishes ───────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85CCD7E912');
        $this->addSql('ALTER TABLE menu_dishes DROP FOREIGN KEY FK_8B0A8B85148EB0CB');
        $this->addSql('ALTER TABLE menu_dishes CHANGE dish_type dish_type ENUM(\'starter\', \'main_course\', \'dessert\') NOT NULL');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT `menu_dishes_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menus (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_dishes ADD CONSTRAINT `menu_dishes_ibfk_2` FOREIGN KEY (dish_id) REFERENCES dishes (id) ON UPDATE CASCADE ON DELETE CASCADE');

        // ── menu_images ───────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE menu_images DROP FOREIGN KEY FK_3358E6B2CCD7E912');
        $this->addSql('ALTER TABLE menu_images ADD CONSTRAINT `menu_images_ibfk_1` FOREIGN KEY (menu_id) REFERENCES menus (id) ON UPDATE CASCADE ON DELETE CASCADE');

        // ── menus ─────────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE menus DROP FOREIGN KEY FK_727508CF35E7D534');
        $this->addSql('ALTER TABLE menus CHANGE description description TEXT DEFAULT NULL, CHANGE min_people min_people INT DEFAULT 6 NOT NULL, CHANGE remaining_quantity remaining_quantity INT DEFAULT 0, CHANGE advance_order_days advance_order_days INT DEFAULT 2 NOT NULL');
        $this->addSql('ALTER TABLE menus ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (regime_id) REFERENCES regimes (id) ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE menus RENAME INDEX UNIQ_727508CF2B36786B TO uq_menu_title');

        // ── order_menus ───────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE order_menus RENAME INDEX IDX_F1EB7C328D9F6D38 TO order_id');
        $this->addSql('ALTER TABLE order_menus RENAME INDEX IDX_F1EB7C32CCD7E912 TO menu_id');

        // ── orders ────────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE orders CHANGE order_date order_date DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE delivery_address delivery_address TEXT NOT NULL, CHANGE equipment_loan equipment_loan TINYINT(1) DEFAULT 0, CHANGE equipment_returned equipment_returned TINYINT(1) DEFAULT 0, CHANGE status status ENUM(\'en attente\', \'acceptée\', \'en préparation\', \'en cours de livraison\', \'livrée\', \'en attente de retour de matériel\', \'terminée\', \'annulée\') DEFAULT \'en attente\'');
        $this->addSql('ALTER TABLE orders RENAME INDEX IDX_E52FFDEEA76ED395 TO user_id');

        // ── regimes ───────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE regimes RENAME INDEX UNIQ_42177456EA750E8 TO uq_regime_label');

        // ── roles ─────────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE roles RENAME INDEX UNIQ_B63E2EC75E237E06 TO uq_role_name');

        // ── users ─────────────────────────────────────────────────────────────
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users CHANGE role_id role_id INT DEFAULT 3 NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE');
        $this->addSql('ALTER TABLE users RENAME INDEX UNIQ_1483A5E9E7927C74 TO uq_email');
    }
}
