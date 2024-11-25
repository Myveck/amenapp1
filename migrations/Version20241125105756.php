<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125105756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classes_backup (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT NOT NULL, nom VARCHAR(30) NOT NULL, INDEX IDX_138321579331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarif_backup (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT DEFAULT NULL, classe VARCHAR(30) NOT NULL, prix_annuel INT NOT NULL, prix_inscription INT NOT NULL, prix_reinscription INT NOT NULL, INDEX IDX_8294172B9331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classes_backup ADD CONSTRAINT FK_138321579331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE tarif_backup ADD CONSTRAINT FK_8294172B9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE ecoles ADD annee_scolaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ecoles ADD CONSTRAINT FK_C46758A29331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('CREATE INDEX IDX_C46758A29331C741 ON ecoles (annee_scolaire_id)');
        $this->addSql('ALTER TABLE eleves_backup ADD annee_scolaire VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE paiements_backup ADD maj VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classes_backup DROP FOREIGN KEY FK_138321579331C741');
        $this->addSql('ALTER TABLE tarif_backup DROP FOREIGN KEY FK_8294172B9331C741');
        $this->addSql('DROP TABLE classes_backup');
        $this->addSql('DROP TABLE tarif_backup');
        $this->addSql('ALTER TABLE ecoles DROP FOREIGN KEY FK_C46758A29331C741');
        $this->addSql('DROP INDEX IDX_C46758A29331C741 ON ecoles');
        $this->addSql('ALTER TABLE ecoles DROP annee_scolaire_id');
        $this->addSql('ALTER TABLE eleves_backup DROP annee_scolaire');
        $this->addSql('ALTER TABLE paiements_backup DROP maj');
    }
}
