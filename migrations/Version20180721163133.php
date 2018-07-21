<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180721163133 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE callback (id VARCHAR(255) NOT NULL, parent_id VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, date DATE NOT NULL, message_id INT NOT NULL, type INT NOT NULL, INDEX IDX_79F97426727ACA70 (parent_id), INDEX IDX_79F97426A76ED395 (user_id), INDEX callback_id_index (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE callback_payload (id INT AUTO_INCREMENT NOT NULL, callback_id VARCHAR(255) DEFAULT NULL, uri VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D705DEEED5B30951 (callback_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT NOT NULL, should_be_notified TINYINT(1) NOT NULL, is_admin TINYINT(1) NOT NULL, INDEX search_idx (should_be_notified), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE callback ADD CONSTRAINT FK_79F97426727ACA70 FOREIGN KEY (parent_id) REFERENCES callback (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE callback ADD CONSTRAINT FK_79F97426A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE callback_payload ADD CONSTRAINT FK_D705DEEED5B30951 FOREIGN KEY (callback_id) REFERENCES callback (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE callback DROP FOREIGN KEY FK_79F97426727ACA70');
        $this->addSql('ALTER TABLE callback_payload DROP FOREIGN KEY FK_D705DEEED5B30951');
        $this->addSql('ALTER TABLE callback DROP FOREIGN KEY FK_79F97426A76ED395');
        $this->addSql('DROP TABLE callback');
        $this->addSql('DROP TABLE callback_payload');
        $this->addSql('DROP TABLE user');
    }
}
