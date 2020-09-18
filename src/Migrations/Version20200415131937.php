<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200415131937 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE booking');
        $this->addSql('ALTER TABLE appointment ADD location VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BBF396750');
        $this->addSql('ALTER TABLE history ADD appointment_id INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('CREATE INDEX IDX_27BA704BE5B533F9 ON history (appointment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE booking (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEBF396750 FOREIGN KEY (id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment DROP location');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BE5B533F9');
        $this->addSql('DROP INDEX IDX_27BA704BE5B533F9 ON history');
        $this->addSql('ALTER TABLE history DROP appointment_id, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BBF396750 FOREIGN KEY (id) REFERENCES appointment (id) ON DELETE CASCADE');
    }
}
