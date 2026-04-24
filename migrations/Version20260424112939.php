<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424112939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE call_log ADD from_extension_id INT DEFAULT NULL, ADD to_extension_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE call_log ADD CONSTRAINT FK_D663C42E35540F0D FOREIGN KEY (from_extension_id) REFERENCES extension (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE call_log ADD CONSTRAINT FK_D663C42EDC877637 FOREIGN KEY (to_extension_id) REFERENCES extension (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D663C42E35540F0D ON call_log (from_extension_id)');
        $this->addSql('CREATE INDEX IDX_D663C42EDC877637 ON call_log (to_extension_id)');
        $this->addSql('ALTER TABLE extension DROP FOREIGN KEY `FK_9FB73D77A76ED395`');
        $this->addSql('DROP INDEX IDX_9FB73D77A76ED395 ON extension');
        $this->addSql('ALTER TABLE extension DROP user_id, CHANGE is_active is_active TINYINT NOT NULL, CHANGE status status VARCHAR(30) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE call_log DROP FOREIGN KEY FK_D663C42E35540F0D');
        $this->addSql('ALTER TABLE call_log DROP FOREIGN KEY FK_D663C42EDC877637');
        $this->addSql('DROP INDEX IDX_D663C42E35540F0D ON call_log');
        $this->addSql('DROP INDEX IDX_D663C42EDC877637 ON call_log');
        $this->addSql('ALTER TABLE call_log DROP from_extension_id, DROP to_extension_id');
        $this->addSql('ALTER TABLE extension ADD user_id INT DEFAULT NULL, CHANGE status status VARCHAR(30) DEFAULT NULL, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE extension ADD CONSTRAINT `FK_9FB73D77A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9FB73D77A76ED395 ON extension (user_id)');
    }
}
