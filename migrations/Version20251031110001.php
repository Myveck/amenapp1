<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031110001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575DAD0CFBF');
        $this->addSql('DROP TABLE evaluation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, examination_id INT DEFAULT NULL, INDEX IDX_1323A575DAD0CFBF (examination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575DAD0CFBF FOREIGN KEY (examination_id) REFERENCES examinations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
