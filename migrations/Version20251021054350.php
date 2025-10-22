<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021054350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eleves ADD annee_scolaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eleves ADD CONSTRAINT FK_383B09B19331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('CREATE INDEX IDX_383B09B19331C741 ON eleves (annee_scolaire_id)');
        $this->addSql('ALTER TABLE emplois_du_temps DROP FOREIGN KEY FK_1FC0BCE18F5EA509');
        $this->addSql('DROP INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps');
        $this->addSql('ALTER TABLE emplois_du_temps DROP classe_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eleves DROP FOREIGN KEY FK_383B09B19331C741');
        $this->addSql('DROP INDEX IDX_383B09B19331C741 ON eleves');
        $this->addSql('ALTER TABLE eleves DROP annee_scolaire_id');
        $this->addSql('ALTER TABLE emplois_du_temps ADD classe_id INT NOT NULL');
        $this->addSql('ALTER TABLE emplois_du_temps ADD CONSTRAINT FK_1FC0BCE18F5EA509 FOREIGN KEY (classe_id) REFERENCES classes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1FC0BCE18F5EA509 ON emplois_du_temps (classe_id)');
    }
}
