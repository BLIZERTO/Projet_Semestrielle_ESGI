<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240302200716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE contract_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('ALTER TABLE contract ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN contract.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE invoice ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN invoice.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE payment ALTER invoice_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN payment.invoice_id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE contract_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE contract ALTER id TYPE INT');
        $this->addSql('COMMENT ON COLUMN contract.id IS NULL');
        $this->addSql('ALTER TABLE invoice ALTER id TYPE INT');
        $this->addSql('COMMENT ON COLUMN invoice.id IS NULL');
        $this->addSql('ALTER TABLE payment ALTER invoice_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN payment.invoice_id IS NULL');
    }
}
