<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119135658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE warranty_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE warranty (id INT NOT NULL, item_id INT NOT NULL, expiration DATE NOT NULL, notify_days_before INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D71CF2126F525E ON warranty (item_id)');
        $this->addSql('ALTER TABLE warranty ADD CONSTRAINT FK_88D71CF2126F525E FOREIGN KEY (item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item DROP files');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE warranty_id_seq CASCADE');
        $this->addSql('ALTER TABLE warranty DROP CONSTRAINT FK_88D71CF2126F525E');
        $this->addSql('DROP TABLE warranty');
        $this->addSql('ALTER TABLE item ADD files TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.files IS \'(DC2Type:simple_array)\'');
    }
}
