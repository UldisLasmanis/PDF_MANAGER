<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106104444 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX i_pdf_id ON attachment (pdf_id)');
        $this->addSql('CREATE INDEX i_pdf_id ON image (pdf_id)');
        $this->addSql('CREATE INDEX i_filename_hash ON pdf (filename_hash)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX i_pdf_id ON attachment');
        $this->addSql('DROP INDEX i_pdf_id ON image');
        $this->addSql('DROP INDEX i_filename_hash ON pdf');
    }
}
