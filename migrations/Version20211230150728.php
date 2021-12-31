<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211230150728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_d8cd66b95e237e066de440267aa6c52f ON hosts');
        $this->addSql('CREATE FULLTEXT INDEX IDX_D8CD66B95E237E06EB78CFF17AA6C52F ON hosts (name, Description, legal_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_d8cd66b95e237e06eb78cff17aa6c52f ON hosts');
        $this->addSql('CREATE FULLTEXT INDEX IDX_D8CD66B95E237E066DE440267AA6C52F ON hosts (name, description, legal_number)');
    }
}
