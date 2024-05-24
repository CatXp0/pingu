<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Product\ProductReviewType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductReviewTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('affectiveItemsAnalysisRating', NumberType::class, [
                'label' => 'Affective Items Analysis Rating',
                'required' => false,
                'scale' => 2,
                'disabled' => true,
            ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductReviewType::class];
    }
}
