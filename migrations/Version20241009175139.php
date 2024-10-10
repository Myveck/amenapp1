<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009175139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annee_scolaire (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(11) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE series (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matieres (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classes (id INT AUTO_INCREMENT NOT NULL, serie_id INT DEFAULT NULL, annee_scolaire_id INT NOT NULL, nom VARCHAR(255) NOT NULL, niveau VARCHAR(255) NOT NULL, classe_order INT NOT NULL, INDEX IDX_2ED7EC5D94388BD (serie_id), INDEX IDX_2ED7EC59331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classes_matieres (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, matiere_id INT NOT NULL, annee_scolaire_id INT NOT NULL, coefficient INT NOT NULL, INDEX IDX_C76295CB8F5EA509 (classe_id), INDEX IDX_C76295CBF46CD258 (matiere_id), INDEX IDX_C76295CB9331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleves (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sexe VARCHAR(3) NOT NULL, lieu_de_naissance VARCHAR(255) DEFAULT NULL, INDEX IDX_383B09B18F5EA509 (classe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleves_backup (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, classe VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ecoles (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, school_name VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) NOT NULL, boite_postale VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) NOT NULL, cellulaire VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enseignants (id INT AUTO_INCREMENT NOT NULL, matiere_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sexe VARCHAR(3) NOT NULL, INDEX IDX_BA5EFB5AF46CD258 (matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parents (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, profession VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parents_eleves (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, parent_id INT NOT NULL, INDEX IDX_1D99ED6DA6CC7B2 (eleve_id), INDEX IDX_1D99ED6D727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiements (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, annee_scolaire_id INT NOT NULL, montant INT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E1B02E12A6CC7B2 (eleve_id), INDEX IDX_E1B02E129331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paiements_backup (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT NOT NULL, eleve_backup_id INT NOT NULL, montant INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7520F1C79331C741 (annee_scolaire_id), INDEX IDX_7520F1C7C4C13F8A (eleve_backup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarif (id INT AUTO_INCREMENT NOT NULL, classe_id INT DEFAULT NULL, annee_scolaire_id INT NOT NULL, prix_annuel INT NOT NULL, prix_inscription INT NOT NULL, prix_reinscription INT NOT NULL, UNIQUE INDEX UNIQ_E7189C98F5EA509 (classe_id), INDEX IDX_E7189C99331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_examens (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, type_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evaluations (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT NOT NULL, nom VARCHAR(30) NOT NULL, INDEX IDX_3B72691D9331C741 (annee_scolaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE examinations (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, evaluation_id INT NOT NULL, matiere_id INT NOT NULL, date_examination DATE NOT NULL, trimestre INT NOT NULL, INDEX IDX_F0CC239F8F5EA509 (classe_id), INDEX IDX_F0CC239F456C5646 (evaluation_id), INDEX IDX_F0CC239FF46CD258 (matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, eleve_id INT NOT NULL, matiere_id INT NOT NULL, evaluation_id INT NOT NULL, examinations_id INT NOT NULL, note DOUBLE PRECISION NOT NULL, date_evaluation DATE NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', trimestre INT NOT NULL, INDEX IDX_11BA68CA6CC7B2 (eleve_id), INDEX IDX_11BA68CF46CD258 (matiere_id), INDEX IDX_11BA68C456C5646 (evaluation_id), INDEX IDX_11BA68CBC75C3F7 (examinations_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplois_du_temps (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, jour VARCHAR(255) NOT NULL, heure_debut TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', heure_fin TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_1FC0BCE18F5EA509 (classe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplois_du_temps_matieres (emplois_du_temps_id INT NOT NULL, matieres_id INT NOT NULL, INDEX IDX_31DE4F371D755CBC (emplois_du_temps_id), INDEX IDX_31DE4F3782350831 (matieres_id), PRIMARY KEY(emplois_du_temps_id, matieres_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classes ADD CONSTRAINT FK_2ED7EC5D94388BD FOREIGN KEY (serie_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE classes ADD CONSTRAINT FK_2ED7EC59331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE classes_matieres ADD CONSTRAINT FK_C76295CB8F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE classes_matieres ADD CONSTRAINT FK_C76295CBF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('ALTER TABLE classes_matieres ADD CONSTRAINT FK_C76295CB9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE eleves ADD CONSTRAINT FK_383B09B18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE emplois_du_temps_matieres ADD CONSTRAINT FK_31DE4F371D755CBC FOREIGN KEY (emplois_du_temps_id) REFERENCES emplois_du_temps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emplois_du_temps_matieres ADD CONSTRAINT FK_31DE4F3782350831 FOREIGN KEY (matieres_id) REFERENCES matieres (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enseignants ADD CONSTRAINT FK_BA5EFB5AF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('ALTER TABLE evaluations ADD CONSTRAINT FK_3B72691D9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE examinations ADD CONSTRAINT FK_F0CC239F8F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE examinations ADD CONSTRAINT FK_F0CC239F456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluations (id)');
        $this->addSql('ALTER TABLE examinations ADD CONSTRAINT FK_F0CC239FF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluations (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CBC75C3F7 FOREIGN KEY (examinations_id) REFERENCES examinations (id)');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E12A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id)');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E129331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE paiements_backup ADD CONSTRAINT FK_7520F1C79331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE paiements_backup ADD CONSTRAINT FK_7520F1C7C4C13F8A FOREIGN KEY (eleve_backup_id) REFERENCES eleves_backup (id)');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6DA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6D727ACA70 FOREIGN KEY (parent_id) REFERENCES parents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C98F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C99331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classes DROP FOREIGN KEY FK_2ED7EC5D94388BD');
        $this->addSql('ALTER TABLE classes DROP FOREIGN KEY FK_2ED7EC59331C741');
        $this->addSql('ALTER TABLE classes_matieres DROP FOREIGN KEY FK_C76295CB8F5EA509');
        $this->addSql('ALTER TABLE classes_matieres DROP FOREIGN KEY FK_C76295CBF46CD258');
        $this->addSql('ALTER TABLE classes_matieres DROP FOREIGN KEY FK_C76295CB9331C741');
        $this->addSql('ALTER TABLE eleves DROP FOREIGN KEY FK_383B09B18F5EA509');
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE18F5EA509');
        $this->addSql('ALTER TABLE emplois_du_temps_matieres DROP FOREIGN KEY FK_31DE4F371D755CBC');
        $this->addSql('ALTER TABLE emplois_du_temps_matieres DROP FOREIGN KEY FK_31DE4F3782350831');
        $this->addSql('ALTER TABLE enseignants DROP FOREIGN KEY FK_BA5EFB5AF46CD258');
        $this->addSql('ALTER TABLE evaluations DROP FOREIGN KEY FK_3B72691D9331C741');
        $this->addSql('ALTER TABLE examinations DROP FOREIGN KEY FK_F0CC239F8F5EA509');
        $this->addSql('ALTER TABLE examinations DROP FOREIGN KEY FK_F0CC239F456C5646');
        $this->addSql('ALTER TABLE examinations DROP FOREIGN KEY FK_F0CC239FF46CD258');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA6CC7B2');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF46CD258');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C456C5646');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CBC75C3F7');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E12A6CC7B2');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E129331C741');
        $this->addSql('ALTER TABLE paiements_backup DROP FOREIGN KEY FK_7520F1C79331C741');
        $this->addSql('ALTER TABLE paiements_backup DROP FOREIGN KEY FK_7520F1C7C4C13F8A');
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6DA6CC7B2');
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6D727ACA70');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C98F5EA509');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C99331C741');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE emplois_du_temps_matieres');
        $this->addSql('DROP TABLE emplois_du_temps');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE examinations');
        $this->addSql('DROP TABLE evaluations');
        $this->addSql('DROP TABLE type_examens');
        $this->addSql('DROP TABLE tarif');
        $this->addSql('DROP TABLE paiements_backup');
        $this->addSql('DROP TABLE paiements');
        $this->addSql('DROP TABLE parents_eleves');
        $this->addSql('DROP TABLE parents');
        $this->addSql('DROP TABLE enseignants');
        $this->addSql('DROP TABLE ecoles');
        $this->addSql('DROP TABLE eleves_backup');
        $this->addSql('DROP TABLE eleves');
        $this->addSql('DROP TABLE classes_matieres');
        $this->addSql('DROP TABLE classes');
        $this->addSql('DROP TABLE matieres');
        $this->addSql('DROP TABLE series');
        $this->addSql('DROP TABLE annee_scolaire');
    }
}
