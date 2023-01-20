<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230120110653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD vendor VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD price INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD currency VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD barcode VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD note VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE item DROP vendor');
        $this->addSql('ALTER TABLE item DROP price');
        $this->addSql('ALTER TABLE item DROP currency');
        $this->addSql('ALTER TABLE item DROP barcode');
        $this->addSql('ALTER TABLE item DROP note');
    }
}
