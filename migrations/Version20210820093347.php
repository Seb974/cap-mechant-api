<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820093347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_group');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3DA5256D');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADB2A824D8');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADDCD6110');
        $this->addSql('DROP INDEX UNIQ_D34A04ADDCD6110 ON product');
        $this->addSql('DROP INDEX UNIQ_D34A04AD3DA5256D ON product');
        $this->addSql('DROP INDEX IDX_D34A04ADB2A824D8 ON product');
        $this->addSql('ALTER TABLE product DROP image_id, DROP tax_id, DROP stock_id, DROP discount, DROP offer_end, DROP full_description, DROP new, DROP stock_managed, DROP require_legal_age, DROP product_group, DROP is_mixed, DROP is_fabricated, DROP is_sold, DROP last_cost, DROP require_declaration, DROP content_weight, DROP accounting_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_group (product_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_CC9C3F994584665A (product_id), INDEX IDX_CC9C3F99FE54D947 (group_id), PRIMARY KEY(product_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_group ADD CONSTRAINT FK_CC9C3F994584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_group ADD CONSTRAINT FK_CC9C3F99FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD image_id INT DEFAULT NULL, ADD tax_id INT DEFAULT NULL, ADD stock_id INT DEFAULT NULL, ADD discount DOUBLE PRECISION DEFAULT NULL, ADD offer_end DATETIME DEFAULT NULL, ADD full_description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD new TINYINT(1) DEFAULT NULL, ADD stock_managed TINYINT(1) DEFAULT NULL, ADD require_legal_age TINYINT(1) DEFAULT NULL, ADD product_group VARCHAR(60) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD is_mixed TINYINT(1) DEFAULT NULL, ADD is_fabricated TINYINT(1) DEFAULT NULL, ADD is_sold TINYINT(1) DEFAULT NULL, ADD last_cost DOUBLE PRECISION DEFAULT NULL, ADD require_declaration TINYINT(1) DEFAULT NULL, ADD content_weight DOUBLE PRECISION DEFAULT NULL, ADD accounting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3DA5256D FOREIGN KEY (image_id) REFERENCES picture (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADB2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADDCD6110 ON product (stock_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD3DA5256D ON product (image_id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADB2A824D8 ON product (tax_id)');
    }
}
