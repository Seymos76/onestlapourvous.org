<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200420164157 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history ADD booking_history_id INT NOT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B879FE723 FOREIGN KEY (booking_history_id) REFERENCES booking_history (id)');
        $this->addSql('CREATE INDEX IDX_27BA704B879FE723 ON history (booking_history_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B879FE723');
        $this->addSql('DROP INDEX IDX_27BA704B879FE723 ON history');
        $this->addSql('ALTER TABLE history DROP booking_history_id');
    }
}
