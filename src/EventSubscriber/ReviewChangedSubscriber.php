<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Product\ProductReview;
use App\Message\AddAffectiveItemsAnalysisRating;
use App\Message\DeleteAffectiveItemsAnalysisRating;
use App\Message\UpdateAffectiveItemsAnalysisRating;
use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ReviewChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
           'sylius.product_review.post_update' => 'onUpdate',
           'sylius.product_review.post_create' => 'onCreate',
           'sylius.product_review.post_delete' => 'onDelete',
        ];
    }

    public function onCreate(ResourceControllerEvent|BlockEvent $event): void
    {
        if (!$event instanceof ResourceControllerEvent) {
            return;
        }

        /** @var ProductReview $productReview */
        $productReview = $event->getSubject();

        $message = new AddAffectiveItemsAnalysisRating(
            $productReview->getId(),
            $productReview->getReviewSubject()->getId(),
            $productReview->getComment(),
        );

        $this->messageBus->dispatch($message);
    }

    public function onUpdate(ResourceControllerEvent|BlockEvent $event): void
    {
        if (!$event instanceof ResourceControllerEvent) {
            return;
        }

        /** @var ProductReview $productReview */
        $productReview = $event->getSubject();

        $message = new UpdateAffectiveItemsAnalysisRating(
            $productReview->getId(),
            $productReview->getReviewSubject()->getId(),
            $productReview->getComment(),
        );

        $this->messageBus->dispatch($message);
    }

    public function onDelete(ResourceControllerEvent|BlockEvent $event): void
    {
        if (!$event instanceof ResourceControllerEvent) {
            return;
        }

        /** @var ProductReview $productReview */
        $productReview = $event->getSubject();

        $message = new DeleteAffectiveItemsAnalysisRating(
            null,
            $productReview->getReviewSubject()->getId(),
            null,
        );

        $this->messageBus->dispatch($message);
    }
}
