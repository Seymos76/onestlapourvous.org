<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200411105124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appointment ADD discriminator VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE history DROP booked, DROP booking_date, DROP location, DROP booking_start, DROP booking_end, DROP cancelled, DROP cancel_message, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BBF396750 FOREIGN KEY (id) REFERENCES appointment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appointment DROP discriminator');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BBF396750');
        $this->addSql('ALTER TABLE history ADD booked TINYINT(1) NOT NULL, ADD booking_date DATE NOT NULL, ADD location VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD booking_start TIME NOT NULL, ADD booking_end TIME NOT NULL, ADD cancelled TINYINT(1) NOT NULL, ADD cancel_message LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
