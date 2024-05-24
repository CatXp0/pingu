<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\PinguApiClient;
use App\DTO\AffectiveItemsAnalysis;
use App\DTO\RatingProbability;
use App\Exception\ApiRequestFailedException;
use App\Request\GetRealRatingForProductReviewRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductReviewService
{
    public function __construct(
        private PinguApiClient $client,
        private ParameterBagInterface $params,
    ) {
    }

    /**
     * @throws ApiRequestFailedException
     * @throws \JsonException
     */
    public function getAffectiveItemsAnalysisRating(string $reviewContent): AffectiveItemsAnalysis
    {
        $request = new GetRealRatingForProductReviewRequest(
            $reviewContent,
            $this->params->get('pingu_api.analyis.endpoint'),
        );
        $response = $this->client->request($request);

        $contents = json_decode(
            $response->getBody()->getContents(),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        return new AffectiveItemsAnalysis(
            affectiveItemsAnalysisRating: (int)$contents->affectiveItemsAnalysisRating,
            confidence: $contents->confidence,
            ratingProbability: new RatingProbability(
                $contents->probabilities->{"1"},
                $contents->probabilities->{"2"},
                $contents->probabilities->{"3"},
                $contents->probabilities->{"4"},
                $contents->probabilities->{"5"},
            ),
        );
    }
}
