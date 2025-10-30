<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026155504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE examinations ADD annee_scolaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE examinations ADD CONSTRAINT FK_F0CC239F9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('CREATE INDEX IDX_F0CC239F9331C741 ON examinations (annee_scolaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE examinations DROP FOREIGN KEY FK_F0CC239F9331C741');
        $this->addSql('DROP INDEX IDX_F0CC239F9331C741 ON examinations');
        $this->addSql('ALTER TABLE examinations DROP annee_scolaire_id');
    }
}
