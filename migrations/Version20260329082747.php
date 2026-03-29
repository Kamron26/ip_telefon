<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329082747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recording (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) NOT NULL, file_size INT DEFAULT NULL, created_at DATETIME NOT NULL, cal_id INT DEFAULT NULL, INDEX IDX_BB532B537300D633 (cal_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE recording ADD CONSTRAINT FK_BB532B537300D633 FOREIGN KEY (cal_id) REFERENCES call_log (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recording DROP FOREIGN KEY FK_BB532B537300D633');
        $this->addSql('DROP TABLE recording');
    }
}
