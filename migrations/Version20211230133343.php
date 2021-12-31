<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211230133343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hosts ADD logo_filename VARCHAR(255) NOT NULL');
        $this->addSql('CREATE FULLTEXT INDEX IDX_D8CD66B95E237E066DE440267AA6C52F ON hosts (name, description, legal_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_D8CD66B95E237E066DE440267AA6C52F ON hosts');
        $this->addSql('ALTER TABLE hosts DROP logo_filename');
    }
}
