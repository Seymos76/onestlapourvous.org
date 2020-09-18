<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200415133124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history ADD patient_id INT DEFAULT NULL, ADD therapist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B43E8B094 FOREIGN KEY (therapist_id) REFERENCES therapist (id)');
        $this->addSql('CREATE INDEX IDX_27BA704B6B899279 ON history (patient_id)');
        $this->addSql('CREATE INDEX IDX_27BA704B43E8B094 ON history (therapist_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B6B899279');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B43E8B094');
        $this->addSql('DROP INDEX IDX_27BA704B6B899279 ON history');
        $this->addSql('DROP INDEX IDX_27BA704B43E8B094 ON history');
        $this->addSql('ALTER TABLE history DROP patient_id, DROP therapist_id');
    }
}
