<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200425121747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users_history DROP FOREIGN KEY FK_D7BA04811E058452');
        $this->addSql('DROP INDEX UNIQ_D7BA04811E058452 ON users_history');
        $this->addSql('ALTER TABLE users_history ADD patient_first_name VARCHAR(255) DEFAULT NULL, ADD patient_last_name VARCHAR(255) DEFAULT NULL, ADD therapist_first_name VARCHAR(255) NOT NULL, ADD therapist_last_name VARCHAR(255) NOT NULL, DROP history_id');
        $this->addSql('ALTER TABLE history ADD users_history_id INT NOT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BC96E2BC6 FOREIGN KEY (users_history_id) REFERENCES users_history (id)');
        $this->addSql('CREATE INDEX IDX_27BA704BC96E2BC6 ON history (users_history_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BC96E2BC6');
        $this->addSql('DROP INDEX IDX_27BA704BC96E2BC6 ON history');
        $this->addSql('ALTER TABLE history DROP users_history_id');
        $this->addSql('ALTER TABLE users_history ADD history_id INT NOT NULL, DROP patient_first_name, DROP patient_last_name, DROP therapist_first_name, DROP therapist_last_name');
        $this->addSql('ALTER TABLE users_history ADD CONSTRAINT FK_D7BA04811E058452 FOREIGN KEY (history_id) REFERENCES history (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D7BA04811E058452 ON users_history (history_id)');
    }
}
