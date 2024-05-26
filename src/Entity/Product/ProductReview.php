<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductReview as BaseProductReview;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_review")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_review')]
class ProductReview extends BaseProductReview
{
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $affectiveItemsAnalysisRating = null;

    public function getAffectiveItemsAnalysisRating(): ?float
    {
        return $this->affectiveItemsAnalysisRating  ;
    }

    public function setAffectiveItemsAnalysisRating(?float $affectiveItemsAnalysisRating): void
    {
        $this->affectiveItemsAnalysisRating = $affectiveItemsAnalysisRating;
    }

    public function getReviewSubject(): ?Product
    {
        $reviewSubject = parent::getReviewSubject();

        if ($reviewSubject instanceof Product) {
            return $reviewSubject;
        }

        return null;
    }
}
