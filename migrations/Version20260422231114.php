<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422231114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extension DROP FOREIGN KEY `FK_9FB73D77A76ED395`');
        $this->addSql('ALTER TABLE extension ADD name VARCHAR(255) DEFAULT NULL, ADD caller_id VARCHAR(255) DEFAULT NULL, ADD is_active TINYINT DEFAULT 1 NOT NULL, ADD status VARCHAR(30) DEFAULT NULL, ADD created_at DATETIME NOT NULL, CHANGE number number VARCHAR(20) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE extension ADD CONSTRAINT FK_9FB73D77A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FB73D7796901F54 ON extension (number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extension DROP FOREIGN KEY FK_9FB73D77A76ED395');
        $this->addSql('DROP INDEX UNIQ_9FB73D7796901F54 ON extension');
        $this->addSql('ALTER TABLE extension DROP name, DROP caller_id, DROP is_active, DROP status, DROP created_at, CHANGE number number INT NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE extension ADD CONSTRAINT `FK_9FB73D77A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
