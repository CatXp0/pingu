<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Product\Product;
use App\Entity\Product\ProductReview;
use App\Message\AddAffectiveItemsAnalysisRating;
use App\Message\DeleteAffectiveItemsAnalysisRating;
use App\Message\UpdateAffectiveItemsAnalysisRating;
use App\Service\ProductReviewService;
use App\Updater\AffectiveItemsAnalysisAverageRatingUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductReviewRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class ModifyAffectiveItemsAnalysisRatingHandler
{
    public function __construct(
        private readonly ProductReviewService $productReviewService,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ProductRepository $productRepository,
        private readonly AffectiveItemsAnalysisAverageRatingUpdater $averageRatingUpdater,
        private readonly EntityManagerInterface $reviewSubjectManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsMessageHandler]
    public function handleCreate(AddAffectiveItemsAnalysisRating $message): void
    {
        /**
         * Cautam feedback-ul dupa id
         * @var ProductReview $review
         */
        $review = $this->productReviewRepository->find($message->getReviewId());

        try {
            // facem request pentru scorul feedback-ului
            $affectiveItemsAnalysisRating = $this->productReviewService->getAffectiveItemsAnalysisRating(
                $message->getReviewContent(),
            );

            // setam in baza de date scorul
            $review->setAffectiveItemsAnalysisRating(
                $affectiveItemsAnalysisRating->ratingProbability->getProbabilityRating(),
            );

            $this->reviewSubjectManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('Could not get affective items analysis rating for product review', [
                'review' => $message->getReviewContent(),
                'reviewId' => $message->getReviewId(),
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }
    }

    #[AsMessageHandler]
    public function handleUpdate(UpdateAffectiveItemsAnalysisRating $message): void
    {
        /**
         * Cautam feedback-ul dupa id
         * @var ProductReview $review
         */
        $review = $this->productReviewRepository->find($message->getReviewId());

        try {
            // facem request pentru scorul feedback-ului
            $affectiveItemsAnalysisRating = $this->productReviewService->getAffectiveItemsAnalysisRating(
                $message->getReviewContent(),
            );
            // setam in baza de date scorul
            $review->setAffectiveItemsAnalysisRating(
                $affectiveItemsAnalysisRating->ratingProbability->getProbabilityRating(),
            );
            // recalculam scorul total al produsului
            $this->averageRatingUpdater->updateFromReview($review);
        } catch (\Throwable $exception) {
            $this->logger->error('Could not get affective items analysis rating for product review', [
                'review' => $message->getReviewContent(),
                'reviewId' => $message->getReviewId(),
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }
    }

    #[AsMessageHandler]
    public function handleDelete(DeleteAffectiveItemsAnalysisRating $message): void
    {
        /** @var Product $review */
        $product = $this->productRepository->find($message->getProductId());

        $this->averageRatingUpdater->update($product);
    }
}
