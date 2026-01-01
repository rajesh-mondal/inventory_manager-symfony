<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101061319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD string_val1 VARCHAR(255) DEFAULT NULL, ADD string_val2 VARCHAR(255) DEFAULT NULL, ADD string_val3 VARCHAR(255) DEFAULT NULL, ADD int_val1 INT DEFAULT NULL, ADD int_val2 INT DEFAULT NULL, ADD int_val3 INT DEFAULT NULL, ADD bool_val1 TINYINT(1) DEFAULT NULL, ADD bool_val2 TINYINT(1) DEFAULT NULL, ADD bool_val3 TINYINT(1) DEFAULT NULL, ADD text_val1 LONGTEXT DEFAULT NULL, ADD text_val2 LONGTEXT DEFAULT NULL, ADD text_val3 LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP string_val1, DROP string_val2, DROP string_val3, DROP int_val1, DROP int_val2, DROP int_val3, DROP bool_val1, DROP bool_val2, DROP bool_val3, DROP text_val1, DROP text_val2, DROP text_val3, DROP created_at');
    }
}
