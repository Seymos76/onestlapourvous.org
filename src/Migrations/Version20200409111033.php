<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200409111033 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE town (id INT AUTO_INCREMENT NOT NULL, department_id INT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, zip_codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_4CE6C7A4AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, country VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE town ADD CONSTRAINT FK_4CE6C7A4AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE user ADD town_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64975E23604 FOREIGN KEY (town_id) REFERENCES town (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64975E23604 ON user (town_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64975E23604');
        $this->addSql('ALTER TABLE town DROP FOREIGN KEY FK_4CE6C7A4AE80F5DF');
        $this->addSql('DROP TABLE town');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP INDEX IDX_8D93D64975E23604 ON user');
        $this->addSql('ALTER TABLE user DROP town_id');
    }
}
