<?php

declare(strict_types=1);

namespace App\Controller\Api\Subscriptions;

use App\Repository\SubscriptionRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Subscription extends AbstractController
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository
    ) {
    }

    /**
     * Get a list of all the available subscriptions.
     */
    #[Route('/api/subscriptions', name: 'app_subscriptions_all', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'A list of all the available subscriptions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: \App\Entity\Subscription::class))
        )
    )]
    #[OA\Tag(name: 'Subscriptions')]
    public function all(): JsonResponse
    {
        return $this->json($this->subscriptionRepository->findAll());
    }

    /**
     * Get a single subscription by ID.
     */
    #[Route('/api/subscriptions/{subscription_id}', name: 'app_subscriptions_one', requirements: ['subscription_id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Response in case a subscription with given id exists',
        content: new Model(type: \App\Entity\Subscription::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Subscription Not Found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 404),
                new OA\Property(property: 'message', example: 'Subscription Not Found'),
            ]
        )
    )]
    #[OA\Tag(name: 'Subscriptions')]
    public function one(int $subscription_id): JsonResponse
    {
        $subscription = $this->subscriptionRepository->find($subscription_id);
        if (null === $subscription) {
            return $this->json(['message' => 'Subscription Not Found', 'code' => 404], 404);
        }

        return $this->json($subscription);
    }
}
