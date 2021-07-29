<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210729124521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_seller');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_seller (product_id INT NOT NULL, seller_id INT NOT NULL, INDEX IDX_996692584584665A (product_id), INDEX IDX_996692588DE820D9 (seller_id), PRIMARY KEY(product_id, seller_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product_seller ADD CONSTRAINT FK_996692584584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_seller ADD CONSTRAINT FK_996692588DE820D9 FOREIGN KEY (seller_id) REFERENCES seller (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
