<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029195140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, examination_id INT DEFAULT NULL, INDEX IDX_1323A575DAD0CFBF (examination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575DAD0CFBF FOREIGN KEY (examination_id) REFERENCES examinations (id)');
        $this->addSql('ALTER TABLE examinations DROP FOREIGN KEY FK_F0CC239F456C5646');
        $this->addSql('DROP INDEX IDX_F0CC239F456C5646 ON examinations');
        $this->addSql('ALTER TABLE examinations DROP evaluation_id');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF46CD258');
        $this->addSql('DROP INDEX IDX_11BA68CF46CD258 ON notes');
        $this->addSql('ALTER TABLE notes DROP matiere_id, DROP trimestre');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575DAD0CFBF');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('ALTER TABLE examinations ADD evaluation_id INT NOT NULL');
        $this->addSql('ALTER TABLE examinations ADD CONSTRAINT FK_F0CC239F456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F0CC239F456C5646 ON examinations (evaluation_id)');
        $this->addSql('ALTER TABLE notes ADD matiere_id INT NOT NULL, ADD trimestre INT NOT NULL');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_11BA68CF46CD258 ON notes (matiere_id)');
    }
}
