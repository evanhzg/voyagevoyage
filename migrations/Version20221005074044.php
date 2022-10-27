<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221005074044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cities (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, name VARCHAR(255) NOT NULL, population INT NOT NULL, description LONGTEXT DEFAULT NULL, status TINYINT(1) NOT NULL, INDEX IDX_D95DB16BF92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, capital_id INT NOT NULL, name VARCHAR(255) NOT NULL, language VARCHAR(5) DEFAULT NULL, european TINYINT(1) NOT NULL, time_zone VARCHAR(6) NOT NULL, status TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_5373C966FC2D9FF7 (capital_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE places (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, open_hour TIME DEFAULT NULL, closed_hour TIME DEFAULT NULL, open_days VARCHAR(255) DEFAULT NULL, pricing INT DEFAULT NULL, status TINYINT(1) NOT NULL, INDEX IDX_FEAF6C558BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cities ADD CONSTRAINT FK_D95DB16BF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE country ADD CONSTRAINT FK_5373C966FC2D9FF7 FOREIGN KEY (capital_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE places ADD CONSTRAINT FK_FEAF6C558BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16BF92F3E70');
        $this->addSql('ALTER TABLE country DROP FOREIGN KEY FK_5373C966FC2D9FF7');
        $this->addSql('ALTER TABLE places DROP FOREIGN KEY FK_FEAF6C558BAC62AF');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE places');
    }
}
