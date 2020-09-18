<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410102201 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64975E23604');
        $this->addSql('DROP INDEX IDX_8D93D64975E23604 ON user');
        $this->addSql('ALTER TABLE user ADD scalar_town VARCHAR(100) DEFAULT NULL, ADD scalar_department VARCHAR(100) DEFAULT NULL, DROP town_id, CHANGE department town VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD town_id INT DEFAULT NULL, DROP scalar_town, DROP scalar_department, CHANGE town department VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64975E23604 FOREIGN KEY (town_id) REFERENCES town (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64975E23604 ON user (town_id)');
    }
}
