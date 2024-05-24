<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct
{
    /** @ORM\Column(type="float", nullable=true) */
    private ?float $affectiveItemsAnalysisAverageRating;

    public function getAffectiveItemsAnalysisAverageRating(): ?float
    {
        return $this->affectiveItemsAnalysisAverageRating;
    }

    public function setAffectiveItemsAnalysisAverageRating(float $affectiveItemsAnalysisAverageRating): void
    {
        $this->affectiveItemsAnalysisAverageRating = $affectiveItemsAnalysisAverageRating;
    }

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
