<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009115835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE eleves_backup (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, classe VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiements_backup (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT NOT NULL, eleve_backup_id INT NOT NULL, montant INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7520F1C79331C741 (annee_scolaire_id), INDEX IDX_7520F1C7C4C13F8A (eleve_backup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paiements_backup ADD CONSTRAINT FK_7520F1C79331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE paiements_backup ADD CONSTRAINT FK_7520F1C7C4C13F8A FOREIGN KEY (eleve_backup_id) REFERENCES eleves_backup (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CBC75C3F7 FOREIGN KEY (examinations_id) REFERENCES examinations (id)');
        $this->addSql('CREATE INDEX IDX_11BA68CBC75C3F7 ON notes (examinations_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiements_backup DROP FOREIGN KEY FK_7520F1C79331C741');
        $this->addSql('ALTER TABLE paiements_backup DROP FOREIGN KEY FK_7520F1C7C4C13F8A');
        $this->addSql('DROP TABLE eleves_backup');
        $this->addSql('DROP TABLE paiements_backup');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CBC75C3F7');
        $this->addSql('DROP INDEX IDX_11BA68CBC75C3F7 ON notes');
    }
}
