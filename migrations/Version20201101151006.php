<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201101151006 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pdf (id INT AUTO_INCREMENT NOT NULL, filename_original VARCHAR(100) NOT NULL, filename_md5 VARCHAR(100) NOT NULL, size_in_bytes INT NOT NULL, page_cnt SMALLINT NOT NULL, uploaded_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thumbnail (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(100) NOT NULL, page_nr SMALLINT NOT NULL, uploaded_at DATETIME NOT NULL, size_in_bytes INT NOT NULL, pdf_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pdf');
        $this->addSql('DROP TABLE thumbnail');
    }
}
