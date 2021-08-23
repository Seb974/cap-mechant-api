<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820115522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE good DROP FOREIGN KEY FK_6C844E92498DA827');
        $this->addSql('ALTER TABLE good DROP FOREIGN KEY FK_6C844E925182BFD8');
        $this->addSql('DROP INDEX IDX_6C844E92498DA827 ON good');
        $this->addSql('DROP INDEX IDX_6C844E925182BFD8 ON good');
        $this->addSql('ALTER TABLE good DROP variation_id, DROP size_id, DROP price');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E498DA827');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E5182BFD8');
        $this->addSql('DROP INDEX IDX_1F1B251E498DA827 ON item');
        $this->addSql('DROP INDEX IDX_1F1B251E5182BFD8 ON item');
        $this->addSql('ALTER TABLE item DROP variation_id, DROP size_id, DROP prepared_qty, DROP delivered_qty, DROP price, DROP tax_rate, DROP is_adjourned, DROP stock');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE good ADD variation_id INT DEFAULT NULL, ADD size_id INT DEFAULT NULL, ADD price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE good ADD CONSTRAINT FK_6C844E92498DA827 FOREIGN KEY (size_id) REFERENCES size (id)');
        $this->addSql('ALTER TABLE good ADD CONSTRAINT FK_6C844E925182BFD8 FOREIGN KEY (variation_id) REFERENCES variation (id)');
        $this->addSql('CREATE INDEX IDX_6C844E92498DA827 ON good (size_id)');
        $this->addSql('CREATE INDEX IDX_6C844E925182BFD8 ON good (variation_id)');
        $this->addSql('ALTER TABLE item ADD variation_id INT DEFAULT NULL, ADD size_id INT DEFAULT NULL, ADD prepared_qty DOUBLE PRECISION DEFAULT NULL, ADD delivered_qty DOUBLE PRECISION DEFAULT NULL, ADD price DOUBLE PRECISION DEFAULT NULL, ADD tax_rate DOUBLE PRECISION DEFAULT NULL, ADD is_adjourned TINYINT(1) DEFAULT NULL, ADD stock DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E498DA827 FOREIGN KEY (size_id) REFERENCES size (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E5182BFD8 FOREIGN KEY (variation_id) REFERENCES variation (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E498DA827 ON item (size_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E5182BFD8 ON item (variation_id)');
    }
}
