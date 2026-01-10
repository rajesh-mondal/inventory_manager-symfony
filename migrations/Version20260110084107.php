<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260110084107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory_write_access (inventory_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FC66682E9EEA759 (inventory_id), INDEX IDX_FC66682EA76ED395 (user_id), PRIMARY KEY(inventory_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inventory_write_access ADD CONSTRAINT FK_FC66682E9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory_write_access ADD CONSTRAINT FK_FC66682EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory_write_access DROP FOREIGN KEY FK_FC66682E9EEA759');
        $this->addSql('ALTER TABLE inventory_write_access DROP FOREIGN KEY FK_FC66682EA76ED395');
        $this->addSql('DROP TABLE inventory_write_access');
    }
}
