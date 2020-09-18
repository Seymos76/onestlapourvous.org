<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200402034427 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP ethic_entity_code_label, DROP school_entity_label, DROP has_certification, DROP is_supervised, DROP is_respecting_ethical_frame_work');
        $this->addSql('ALTER TABLE therapist ADD email VARCHAR(180) NOT NULL, ADD roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD password VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD email_token VARCHAR(255) NOT NULL, ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD country VARCHAR(255) NOT NULL, ADD zip_code VARCHAR(255) NOT NULL, ADD phone_number VARCHAR(255) NOT NULL, ADD has_accepted_terms_and_policies TINYINT(1) NOT NULL, ADD ethic_entity_code_label VARCHAR(255) NOT NULL, ADD school_entity_label VARCHAR(255) NOT NULL, ADD has_certification TINYINT(1) NOT NULL, ADD is_supervised TINYINT(1) NOT NULL, ADD is_respecting_ethical_frame_work TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C3D632FE7927C74 ON therapist (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_C3D632FE7927C74 ON therapist');
        $this->addSql('ALTER TABLE therapist DROP email, DROP roles, DROP password, DROP created_at, DROP is_active, DROP email_token, DROP first_name, DROP last_name, DROP country, DROP zip_code, DROP phone_number, DROP has_accepted_terms_and_policies, DROP ethic_entity_code_label, DROP school_entity_label, DROP has_certification, DROP is_supervised, DROP is_respecting_ethical_frame_work');
        $this->addSql('ALTER TABLE user ADD ethic_entity_code_label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD school_entity_label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD has_certification TINYINT(1) NOT NULL, ADD is_supervised TINYINT(1) NOT NULL, ADD is_respecting_ethical_frame_work TINYINT(1) NOT NULL');
    }
}
