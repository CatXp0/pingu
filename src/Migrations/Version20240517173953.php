<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517173953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product CHANGE affective_items_analysis_average_rating affectiveItemsAnalysisAverageRating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_review CHANGE affective_items_analysis_rating affectiveItemsAnalysisRating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product CHANGE affectiveItemsAnalysisAverageRating affective_items_analysis_average_rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_review CHANGE affectiveItemsAnalysisRating affective_items_analysis_rating DOUBLE PRECISION DEFAULT NULL');
    }
}
