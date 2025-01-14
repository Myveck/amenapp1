<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114155139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE18F5EA509');
        $this->addSql('DROP INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps');
        $this->addSql('ALTER TABLE emplois_du_temps DROP classe_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emplois_du_temps ADD classe_id INT NOT NULL');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps (classe_id)');
    }
}
