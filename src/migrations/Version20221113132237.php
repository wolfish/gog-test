<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221113132237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gog_cart (id INT AUTO_INCREMENT NOT NULL, id_session VARCHAR(255) NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_47A77A94ED97CA4 (id_session), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gog_cart_has_products (id INT AUTO_INCREMENT NOT NULL, id_cart INT DEFAULT NULL, id_product INT DEFAULT NULL, quantity INT NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated DATETIME DEFAULT NULL, INDEX IDX_460EF11B808394B5 (id_cart), INDEX IDX_460EF11BDD7ADDD (id_product), UNIQUE INDEX UNIQ_460EF11B808394B5DD7ADDD (id_cart, id_product), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gog_currency (id INT AUTO_INCREMENT NOT NULL, short_name VARCHAR(3) NOT NULL, full_name VARCHAR(255) NOT NULL, symbol VARCHAR(20) NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7697E7EE3EE4B093 (short_name), UNIQUE INDEX UNIQ_7697E7EEECC836F9 (symbol), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gog_products (id INT AUTO_INCREMENT NOT NULL, id_currency INT DEFAULT NULL, title VARCHAR(255) NOT NULL, price INT NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_AC7B358B2B36786B (title), INDEX IDX_AC7B358B398D64AA (id_currency), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gog_cart_has_products ADD CONSTRAINT FK_460EF11B808394B5 FOREIGN KEY (id_cart) REFERENCES gog_cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gog_cart_has_products ADD CONSTRAINT FK_460EF11BDD7ADDD FOREIGN KEY (id_product) REFERENCES gog_products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gog_products ADD CONSTRAINT FK_AC7B358B398D64AA FOREIGN KEY (id_currency) REFERENCES gog_currency (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gog_cart_has_products DROP FOREIGN KEY FK_460EF11B808394B5');
        $this->addSql('ALTER TABLE gog_cart_has_products DROP FOREIGN KEY FK_460EF11BDD7ADDD');
        $this->addSql('ALTER TABLE gog_products DROP FOREIGN KEY FK_AC7B358B398D64AA');
        $this->addSql('DROP TABLE gog_cart');
        $this->addSql('DROP TABLE gog_cart_has_products');
        $this->addSql('DROP TABLE gog_currency');
        $this->addSql('DROP TABLE gog_products');
    }
}
