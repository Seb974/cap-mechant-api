<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820095938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_entity DROP FOREIGN KEY FK_CDA754BD139DF194');
        $this->addSql('ALTER TABLE order_entity DROP FOREIGN KEY FK_CDA754BD36BD1931');
        $this->addSql('ALTER TABLE order_entity DROP FOREIGN KEY FK_CDA754BDCC3C66FC');
        $this->addSql('ALTER TABLE order_entity DROP FOREIGN KEY FK_CDA754BDCF479569');
        $this->addSql('DROP INDEX IDX_CDA754BD139DF194 ON order_entity');
        $this->addSql('DROP INDEX IDX_CDA754BD36BD1931 ON order_entity');
        $this->addSql('DROP INDEX IDX_CDA754BDCF479569 ON order_entity');
        $this->addSql('DROP INDEX IDX_CDA754BDCC3C66FC ON order_entity');
        $this->addSql('ALTER TABLE order_entity DROP catalog_id, DROP promotion_id, DROP applied_condition_id, DROP touring_id, DROP is_remains, DROP total_ht, DROP total_ttc, DROP message, DROP payment_id, DROP uuid, DROP delivery_priority, DROP regulated, DROP track_ids, DROP reservation_number, DROP invoiced');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_entity ADD catalog_id INT DEFAULT NULL, ADD promotion_id INT DEFAULT NULL, ADD applied_condition_id INT DEFAULT NULL, ADD touring_id INT DEFAULT NULL, ADD is_remains TINYINT(1) DEFAULT NULL, ADD total_ht DOUBLE PRECISION DEFAULT NULL, ADD total_ttc DOUBLE PRECISION DEFAULT NULL, ADD message LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD payment_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD uuid CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD delivery_priority INT DEFAULT NULL, ADD regulated TINYINT(1) DEFAULT NULL, ADD track_ids LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD reservation_number VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD invoiced TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_entity ADD CONSTRAINT FK_CDA754BD139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('ALTER TABLE order_entity ADD CONSTRAINT FK_CDA754BD36BD1931 FOREIGN KEY (touring_id) REFERENCES touring (id)');
        $this->addSql('ALTER TABLE order_entity ADD CONSTRAINT FK_CDA754BDCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id)');
        $this->addSql('ALTER TABLE order_entity ADD CONSTRAINT FK_CDA754BDCF479569 FOREIGN KEY (applied_condition_id) REFERENCES `condition` (id)');
        $this->addSql('CREATE INDEX IDX_CDA754BD139DF194 ON order_entity (promotion_id)');
        $this->addSql('CREATE INDEX IDX_CDA754BD36BD1931 ON order_entity (touring_id)');
        $this->addSql('CREATE INDEX IDX_CDA754BDCF479569 ON order_entity (applied_condition_id)');
        $this->addSql('CREATE INDEX IDX_CDA754BDCC3C66FC ON order_entity (catalog_id)');
    }
}
