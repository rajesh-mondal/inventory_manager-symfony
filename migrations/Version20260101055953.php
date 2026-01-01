<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101055953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory ADD is_public TINYINT(1) NOT NULL, ADD id_pattern VARCHAR(255) DEFAULT NULL, ADD tags JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD custom_string1_name VARCHAR(255) DEFAULT NULL, ADD custom_string1_state TINYINT(1) NOT NULL, ADD custom_string2_name VARCHAR(255) DEFAULT NULL, ADD custom_string2_state TINYINT(1) NOT NULL, ADD custom_string3_name VARCHAR(255) DEFAULT NULL, ADD custom_string3_state TINYINT(1) NOT NULL, ADD custom_int1_name VARCHAR(255) DEFAULT NULL, ADD custom_int1_state TINYINT(1) NOT NULL, ADD custom_int2_name VARCHAR(255) DEFAULT NULL, ADD custom_int2_state TINYINT(1) NOT NULL, ADD custom_int3_name VARCHAR(255) DEFAULT NULL, ADD custom_int3_state TINYINT(1) NOT NULL, ADD custom_bool1_name VARCHAR(255) DEFAULT NULL, ADD custom_bool1_state TINYINT(1) NOT NULL, ADD custom_bool2_name VARCHAR(255) DEFAULT NULL, ADD custom_bool2_state TINYINT(1) NOT NULL, ADD custom_bool3_name VARCHAR(255) DEFAULT NULL, ADD custom_bool3_state TINYINT(1) NOT NULL, ADD custom_text1_name VARCHAR(255) DEFAULT NULL, ADD custom_text1_state TINYINT(1) NOT NULL, ADD custom_text2_name VARCHAR(255) DEFAULT NULL, ADD custom_text2_state TINYINT(1) NOT NULL, ADD custom_text3_name VARCHAR(255) DEFAULT NULL, ADD custom_text3_state TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP is_public, DROP id_pattern, DROP tags, DROP custom_string1_name, DROP custom_string1_state, DROP custom_string2_name, DROP custom_string2_state, DROP custom_string3_name, DROP custom_string3_state, DROP custom_int1_name, DROP custom_int1_state, DROP custom_int2_name, DROP custom_int2_state, DROP custom_int3_name, DROP custom_int3_state, DROP custom_bool1_name, DROP custom_bool1_state, DROP custom_bool2_name, DROP custom_bool2_state, DROP custom_bool3_name, DROP custom_bool3_state, DROP custom_text1_name, DROP custom_text1_state, DROP custom_text2_name, DROP custom_text2_state, DROP custom_text3_name, DROP custom_text3_state');
    }
}
