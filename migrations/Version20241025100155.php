<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025100155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX FK_8D9773D29331C741 ON matieres');
        $this->addSql('ALTER TABLE matieres DROP annee_scolaire_id');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CBC75C3F7 FOREIGN KEY (examinations_id) REFERENCES examinations (id)');
        $this->addSql('CREATE INDEX IDX_11BA68CBC75C3F7 ON notes (examinations_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matieres ADD annee_scolaire_id INT NOT NULL');
        $this->addSql('CREATE INDEX FK_8D9773D29331C741 ON matieres (annee_scolaire_id)');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CBC75C3F7');
        $this->addSql('DROP INDEX IDX_11BA68CBC75C3F7 ON notes');
    }
}
