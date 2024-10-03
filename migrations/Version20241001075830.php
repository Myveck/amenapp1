<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001075830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6D727ACA70');
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6DA6CC7B2');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6D727ACA70 FOREIGN KEY (parent_id) REFERENCES parents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6DA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6DA6CC7B2');
        $this->addSql('ALTER TABLE parents_eleves DROP FOREIGN KEY FK_1D99ED6D727ACA70');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6DA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleves (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE parents_eleves ADD CONSTRAINT FK_1D99ED6D727ACA70 FOREIGN KEY (parent_id) REFERENCES parents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
