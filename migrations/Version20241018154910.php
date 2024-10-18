<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018154910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tarif_backup (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT DEFAULT NULL, classe VARCHAR(30) NOT NULL, prix_annuel INT NOT NULL, prix_inscription INT NOT NULL, prix_reinscription INT NOT NULL, INDEX IDX_8294172B9331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tarif_backup ADD CONSTRAINT FK_8294172B9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CBC75C3F7 FOREIGN KEY (examinations_id) REFERENCES examinations (id)');
        $this->addSql('CREATE INDEX IDX_11BA68CBC75C3F7 ON notes (examinations_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarif_backup DROP FOREIGN KEY FK_8294172B9331C741');
        $this->addSql('DROP TABLE tarif_backup');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CBC75C3F7');
        $this->addSql('DROP INDEX IDX_11BA68CBC75C3F7 ON notes');
    }
}
