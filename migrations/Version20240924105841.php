<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240924105841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classes_matieres (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, matiere_id INT NOT NULL, coefficient INT NOT NULL, INDEX IDX_C76295CB8F5EA509 (classe_id), INDEX IDX_C76295CBF46CD258 (matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classes_matieres ADD CONSTRAINT FK_C76295CB8F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id)');
        $this->addSql('ALTER TABLE classes_matieres ADD CONSTRAINT FK_C76295CBF46CD258 FOREIGN KEY (matiere_id) REFERENCES matieres (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classes_matieres DROP FOREIGN KEY FK_C76295CB8F5EA509');
        $this->addSql('ALTER TABLE classes_matieres DROP FOREIGN KEY FK_C76295CBF46CD258');
        $this->addSql('DROP TABLE classes_matieres');
    }
}
