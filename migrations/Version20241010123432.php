<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010123432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pop ADD related_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pop ADD CONSTRAINT FK_19D0B71658D797EA FOREIGN KEY (related_group_id) REFERENCES `group` (id)');
        $this->addSql('CREATE INDEX IDX_19D0B71658D797EA ON pop (related_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pop DROP FOREIGN KEY FK_19D0B71658D797EA');
        $this->addSql('DROP INDEX IDX_19D0B71658D797EA ON pop');
        $this->addSql('ALTER TABLE pop DROP related_group_id');
    }
}
