<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180708093138 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX callback_id_index');
        $this->addSql('DROP INDEX IDX_79F97426A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__callback AS SELECT id, user_id, date, message_id, type FROM callback');
        $this->addSql('DROP TABLE callback');
        $this->addSql('CREATE TABLE callback (id VARCHAR(255) NOT NULL COLLATE BINARY, user_id INTEGER DEFAULT NULL, date DATE NOT NULL, message_id INTEGER NOT NULL, type INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_79F97426A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO callback (id, user_id, date, message_id, type) SELECT id, user_id, date, message_id, type FROM __temp__callback');
        $this->addSql('DROP TABLE __temp__callback');
        $this->addSql('CREATE INDEX callback_id_index ON callback (id)');
        $this->addSql('CREATE INDEX IDX_79F97426A76ED395 ON callback (user_id)');
        $this->addSql('DROP INDEX IDX_D705DEEED5B30951');
        $this->addSql('CREATE TEMPORARY TABLE __temp__callback_payload AS SELECT id, callback_id, uri, name FROM callback_payload');
        $this->addSql('DROP TABLE callback_payload');
        $this->addSql('CREATE TABLE callback_payload (id INTEGER NOT NULL, callback_id VARCHAR(255) DEFAULT NULL COLLATE BINARY, uri VARCHAR(255) NOT NULL COLLATE BINARY, name VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_D705DEEED5B30951 FOREIGN KEY (callback_id) REFERENCES callback (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO callback_payload (id, callback_id, uri, name) SELECT id, callback_id, uri, name FROM __temp__callback_payload');
        $this->addSql('DROP TABLE __temp__callback_payload');
        $this->addSql('CREATE INDEX IDX_D705DEEED5B30951 ON callback_payload (callback_id)');
        $this->addSql('DROP INDEX search_idx');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, should_be_notified, is_admin FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, should_be_notified BOOLEAN NOT NULL, is_admin BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, should_be_notified, is_admin) SELECT id, should_be_notified, is_admin FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX search_idx ON user (should_be_notified)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_79F97426A76ED395');
        $this->addSql('DROP INDEX callback_id_index');
        $this->addSql('CREATE TEMPORARY TABLE __temp__callback AS SELECT id, user_id, date, message_id, type FROM callback');
        $this->addSql('DROP TABLE callback');
        $this->addSql('CREATE TABLE callback (id VARCHAR(255) NOT NULL, user_id INTEGER DEFAULT NULL, date DATE NOT NULL, message_id INTEGER NOT NULL, type VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO callback (id, user_id, date, message_id, type) SELECT id, user_id, date, message_id, type FROM __temp__callback');
        $this->addSql('DROP TABLE __temp__callback');
        $this->addSql('CREATE INDEX IDX_79F97426A76ED395 ON callback (user_id)');
        $this->addSql('CREATE INDEX callback_id_index ON callback (id)');
        $this->addSql('DROP INDEX IDX_D705DEEED5B30951');
        $this->addSql('CREATE TEMPORARY TABLE __temp__callback_payload AS SELECT id, callback_id, uri, name FROM callback_payload');
        $this->addSql('DROP TABLE callback_payload');
        $this->addSql('CREATE TABLE callback_payload (id INTEGER NOT NULL, callback_id VARCHAR(255) DEFAULT NULL, uri VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO callback_payload (id, callback_id, uri, name) SELECT id, callback_id, uri, name FROM __temp__callback_payload');
        $this->addSql('DROP TABLE __temp__callback_payload');
        $this->addSql('CREATE INDEX IDX_D705DEEED5B30951 ON callback_payload (callback_id)');
        $this->addSql('DROP INDEX search_idx');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, should_be_notified, is_admin FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, should_be_notified BOOLEAN NOT NULL, is_admin BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, should_be_notified, is_admin) SELECT id, should_be_notified, is_admin FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX search_idx ON user (should_be_notified)');
    }
}
