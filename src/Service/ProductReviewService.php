<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\PinguApiClient;
use App\DTO\AffectiveItemsAnalysis;
use App\DTO\RatingProbability;
use App\Exception\ApiRequestFailedException;
use App\Request\GetRealRatingForProductReviewRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductReviewService
{
    public function __construct(
        private readonly PinguApiClient $client,
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Metoda care face request pentru un scor al itemilor afectivi ai unui feedback
     * @throws ApiRequestFailedException|\JsonException
     */
    public function getAffectiveItemsAnalysisRating(string $reviewContent): AffectiveItemsAnalysis
    {
        // construim requestul cu continutul feedback-ului si endpointul pentru API
        $request = new GetRealRatingForProductReviewRequest(
            $reviewContent,
            $this->params->get('pingu_api.analyis.endpoint'),
        );

        // facem requestul
        $response = $this->client->request($request)->getBody()->getContents();

        // decodam continutul in obiect
        $contents = json_decode(
            $response,
            false,
            512,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        );
        // returnam DTO AffectiveItemsAnalysis cu datele din API
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
