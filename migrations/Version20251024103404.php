<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024103404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, eleve_id INT DEFAULT NULL, classe_id INT DEFAULT NULL, annee_scolaire_id INT DEFAULT NULL, redouble TINYINT(1) NOT NULL, actif TINYINT(1) NOT NULL, date_inscription DATETIME NOT NULL, moyenne_annuelle DOUBLE PRECISION DEFAULT NULL, INDEX IDX_5E90F6D6A6CC7B2 (eleve_id), INDEX IDX_5E90F6D68F5EA509 (classe_id), INDEX IDX_5E90F6D69331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D68F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D69331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE annee_scolaire ADD actif TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE classes DROP FOREIGN KEY FK_2ED7EC5D94388BD');
        $this->addSql('DROP INDEX IDX_2ED7EC5D94388BD ON classes');
        $this->addSql('ALTER TABLE classes CHANGE serie_id next_classe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE classes ADD CONSTRAINT FK_2ED7EC5C8D4703A FOREIGN KEY (next_classe_id) REFERENCES classes (id)');
        $this->addSql('CREATE INDEX IDX_2ED7EC5C8D4703A ON classes (next_classe_id)');
        $this->addSql('ALTER TABLE eleves ADD annee_scolaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eleves ADD CONSTRAINT FK_383B09B19331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('CREATE INDEX IDX_383B09B19331C741 ON eleves (annee_scolaire_id)');
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE18F5EA509');
        $this->addSql('DROP INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps');
        $this->addSql('ALTER TABLE emplois_du_temps DROP classe_id');
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6DA6CC7B2');
        $this->addSql('DROP INDEX IDX_1D99ED6DA6CC7B2 ON parents_eleves');
        $this->addSql('ALTER TABLE parents_eleves DROP eleve_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6A6CC7B2');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D68F5EA509');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D69331C741');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('ALTER TABLE annee_scolaire DROP actif');
        $this->addSql('ALTER TABLE classes DROP FOREIGN KEY FK_2ED7EC5C8D4703A');
        $this->addSql('DROP INDEX IDX_2ED7EC5C8D4703A ON classes');
        $this->addSql('ALTER TABLE classes CHANGE next_classe_id serie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE classes ADD CONSTRAINT FK_2ED7EC5D94388BD FOREIGN KEY (serie_id) REFERENCES series (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2ED7EC5D94388BD ON classes (serie_id)');
        $this->addSql('ALTER TABLE eleves DROP FOREIGN KEY FK_383B09B19331C741');
        $this->addSql('DROP INDEX IDX_383B09B19331C741 ON eleves');
        $this->addSql('ALTER TABLE eleves ADD classe_id INT NOT NULL, DROP annee_scolaire_id');
        $this->addSql('ALTER TABLE eleves ADD CONSTRAINT FK_383B09B18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_383B09B18F5EA509 ON eleves (classe_id)');
        $this->addSql('ALTER TABLE emplois_du_temps ADD classe_id INT NOT NULL');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps (classe_id)');
        $this->addSql('ALTER TABLE parents_eleves ADD eleve_id INT NOT NULL');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6DA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_1D99ED6DA6CC7B2 ON parents_eleves (eleve_id)');
    }
}
