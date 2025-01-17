<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20250117180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Zmiana nazwy tabeli z portfolios na portfolio';
    }

    public function up(Schema $schema): void
    {
        // Zmieniamy nazwę tabeli
        $this->addSql('ALTER TABLE portfolios RENAME TO portfolio');
    }

    public function down(Schema $schema): void
    {
        // Przywracamy nazwę tabeli do 'portfolios'
        $this->addSql('ALTER TABLE portfolio RENAME TO portfolios');
    }
}
