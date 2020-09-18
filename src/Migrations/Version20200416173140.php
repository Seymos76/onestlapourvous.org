<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200416173140 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BE5B533F9');
        $this->addSql('DROP INDEX IDX_27BA704BE5B533F9 ON history');
        $this->addSql('ALTER TABLE history ADD appointment VARCHAR(255) DEFAULT NULL, DROP appointment_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history ADD appointment_id INT DEFAULT NULL, DROP appointment');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('CREATE INDEX IDX_27BA704BE5B533F9 ON history (appointment_id)');
    }
}
