<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713162209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consumer_category (consumer_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_6BE4592337FDBD6D (consumer_id), INDEX IDX_6BE4592312469DE2 (category_id), PRIMARY KEY (consumer_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consumer_business (consumer_id INT NOT NULL, business_id INT NOT NULL, INDEX IDX_657B2EDA37FDBD6D (consumer_id), INDEX IDX_657B2EDAA89DB457 (business_id), PRIMARY KEY (consumer_id, business_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consumer_category ADD CONSTRAINT FK_6BE4592337FDBD6D FOREIGN KEY (consumer_id) REFERENCES consumer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumer_category ADD CONSTRAINT FK_6BE4592312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumer_business ADD CONSTRAINT FK_657B2EDA37FDBD6D FOREIGN KEY (consumer_id) REFERENCES consumer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumer_business ADD CONSTRAINT FK_657B2EDAA89DB457 FOREIGN KEY (business_id) REFERENCES business (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumer_category DROP FOREIGN KEY FK_6BE4592337FDBD6D');
        $this->addSql('ALTER TABLE consumer_category DROP FOREIGN KEY FK_6BE4592312469DE2');
        $this->addSql('ALTER TABLE consumer_business DROP FOREIGN KEY FK_657B2EDA37FDBD6D');
        $this->addSql('ALTER TABLE consumer_business DROP FOREIGN KEY FK_657B2EDAA89DB457');
        $this->addSql('DROP TABLE consumer_category');
        $this->addSql('DROP TABLE consumer_business');
    }
}
