<?php

namespace App\Controller;

use App\Service\ProductReviewStatisticsService;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

#[AsController]
class ReviewsController extends AbstractController
{
    public function __construct(
        private readonly Environment $templatingEngine,
        private readonly ProductReviewStatisticsService $statisticsDataProvider,
        private readonly ChannelContextInterface $channelContext,
        private readonly RouterInterface $router,
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function renderStatistics(int $id): Response
    {
        $channel = $this->channelContext->getChannel();
        return new Response($this->templatingEngine->render(
            '@SyliusAdmin/Product/Show/Statistics/_reviewsStats.html.twig',
            $this->statisticsDataProvider->getRawData(
                $channel,
                (new \DateTime('first day of january this year')),
                (new \DateTime('first day of january next year')),
                'month',
                $id,
            ),
        ));
    }

    #[Route('/admin/reviews-statistics', name: 'product_reviews_statistics')]
    public function getRawData(Request $request): Response
    {
        $channel = $this->findChannelByCodeOrFindFirst((string) $request->query->get('channelCode'));

        if (null === $channel) {
            return new RedirectResponse($this->router->generate('sylius_admin_channel_create'));
        }

        return new JsonResponse(
            $this->statisticsDataProvider->getRawData(
                $channel,
                (new \DateTime((string) $request->query->get('startDate'))),
                (new \DateTime((string) $request->query->get('endDate'))),
                (string) $request->query->get('interval'),
                (string) $request->query->get('productId'),
            ),
        );
    }

    private function findChannelByCodeOrFindFirst(?string $channelCode): ?ChannelInterface
    {
        if (null !== $channelCode) {
            $channel = $this->channelRepository->findOneByCode($channelCode);
            Assert::nullOrIsInstanceOf($channel, ChannelInterface::class);

            return $channel;
        }

        $channel = $this->channelRepository->findBy([], ['id' => 'ASC'], 1)[0] ?? null;
        Assert::nullOrIsInstanceOf($channel, ChannelInterface::class);

        return $channel;
    }
}
