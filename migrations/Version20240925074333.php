<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925074333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE13633CA6F');
        $this->addSql('DROP INDEX IDX_1FC0BCE13633CA6F ON emplois_du_temps');
        $this->addSql('ALTER TABLE emplois_du_temps CHANGE classe_id_id classe_id INT NOT NULL');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('CREATE INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps (classe_id)');
        $this->addSql('ALTER TABLE enseignants DROP FOREIGN KEY FK_BA5EFB5AF3E43022');
        $this->addSql('DROP INDEX IDX_BA5EFB5AF3E43022 ON enseignants');
        $this->addSql('ALTER TABLE enseignants CHANGE matiere_id_id matiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enseignants ADD CONSTRAINT FK_BA5EFB5AF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('CREATE INDEX IDX_BA5EFB5AF46CD258 ON enseignants (matiere_id)');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C602483BE');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF3E43022');
        $this->addSql('DROP INDEX IDX_11BA68C602483BE ON notes');
        $this->addSql('DROP INDEX IDX_11BA68CF3E43022 ON notes');
        $this->addSql('ALTER TABLE notes ADD eleve_id INT NOT NULL, ADD matiere_id INT NOT NULL, DROP eleve_id_id, DROP matiere_id_id');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('CREATE INDEX IDX_11BA68CA6CC7B2 ON notes (eleve_id)');
        $this->addSql('CREATE INDEX IDX_11BA68CF46CD258 ON notes (matiere_id)');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E12602483BE');
        $this->addSql('DROP INDEX IDX_E1B02E12602483BE ON paiements');
        $this->addSql('ALTER TABLE paiements CHANGE eleve_id_id eleve_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E12A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id)');
        $this->addSql('CREATE INDEX IDX_E1B02E12A6CC7B2 ON paiements (eleve_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA6CC7B2');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF46CD258');
        $this->addSql('DROP INDEX IDX_11BA68CA6CC7B2 ON notes');
        $this->addSql('DROP INDEX IDX_11BA68CF46CD258 ON notes');
        $this->addSql('ALTER TABLE notes ADD eleve_id_id INT NOT NULL, ADD matiere_id_id INT NOT NULL, DROP eleve_id, DROP matiere_id');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C602483BE FOREIGN KEY (eleve_id_id) REFERENCES eleves (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF3E43022 FOREIGN KEY (matiere_id_id) REFERENCES matieres (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_11BA68C602483BE ON notes (eleve_id_id)');
        $this->addSql('CREATE INDEX IDX_11BA68CF3E43022 ON notes (matiere_id_id)');
        $this->addSql('ALTER TABLE enseignants DROP FOREIGN KEY FK_BA5EFB5AF46CD258');
        $this->addSql('DROP INDEX IDX_BA5EFB5AF46CD258 ON enseignants');
        $this->addSql('ALTER TABLE enseignants CHANGE matiere_id matiere_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enseignants ADD CONSTRAINT FK_BA5EFB5AF3E43022 FOREIGN KEY (matiere_id_id) REFERENCES matieres (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BA5EFB5AF3E43022 ON enseignants (matiere_id_id)');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E12A6CC7B2');
        $this->addSql('DROP INDEX IDX_E1B02E12A6CC7B2 ON paiements');
        $this->addSql('ALTER TABLE paiements CHANGE eleve_id eleve_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E12602483BE FOREIGN KEY (eleve_id_id) REFERENCES eleves (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E1B02E12602483BE ON paiements (eleve_id_id)');
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE18F5EA509');
        $this->addSql('DROP INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps');
        $this->addSql('ALTER TABLE emplois_du_temps CHANGE classe_id classe_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE13633CA6F FOREIGN KEY (classe_id_id) REFERENCES classes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1FC0BCE13633CA6F ON emplois_du_temps (classe_id_id)');
    }
}
