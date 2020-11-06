<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105172152 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachment (id INT AUTO_INCREMENT NOT NULL, filename_original VARCHAR(100) NOT NULL, filename_hash VARCHAR(100) NOT NULL, size_in_bytes INT NOT NULL, uploaded_at DATETIME NOT NULL, pdf_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(100) NOT NULL, page_nr SMALLINT NOT NULL, size_in_bytes INT NOT NULL, uploaded_at DATETIME NOT NULL, pdf_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pdf (id INT AUTO_INCREMENT NOT NULL, filename_original VARCHAR(100) NOT NULL, filename_hash VARCHAR(100) NOT NULL, page_cnt SMALLINT NOT NULL, size_in_bytes INT NOT NULL, uploaded_at DATETIME NOT NULL, preview_image_filename VARCHAR(100) NOT NULL, attachment_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE pdf');
    }
}
