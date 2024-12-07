<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205001801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachments (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, file VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, INDEX IDX_47C4FAD6537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domain (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, client_id VARCHAR(16) NOT NULL, smtp_host VARCHAR(255) NOT NULL, smtp_user VARCHAR(255) NOT NULL, smtp_pass VARCHAR(255) NOT NULL, smtp_port INT NOT NULL, use_auth TINYINT(1) NOT NULL, from_email VARCHAR(255) NOT NULL, from_name VARCHAR(255) NOT NULL, from_host VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE history (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, domain_id INT NOT NULL, client_id VARCHAR(16) NOT NULL, orders_id INT DEFAULT NULL, package_id INT DEFAULT NULL, date DATETIME NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_27BA704B537A1329 (message_id), INDEX IDX_27BA704B115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, domain_id INT NOT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, active TINYINT(1) NOT NULL, sent TINYINT(1) NOT NULL, sent_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, recipients JSON NOT NULL, INDEX IDX_B6BD307F115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attachments ADD CONSTRAINT FK_47C4FAD6537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attachments DROP FOREIGN KEY FK_47C4FAD6537A1329');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B537A1329');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B115F0EE5');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F115F0EE5');
        $this->addSql('DROP TABLE attachments');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE history');
        $this->addSql('DROP TABLE message');
    }
}
