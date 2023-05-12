<?php

declare(strict_types=1);

namespace App\Controller\Api\Subscriptions;

use App\Repository\SubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class Subscription extends AbstractController
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository
    ) {}

    #[Route('/api/subscriptions', name: 'app_subscriptions_all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        return $this->json($this->subscriptionRepository->findAll());
    }

    #[Route('/api/subscriptions/{subscription_id}', name: 'app_subscriptions_one',  requirements: ['subscription_id' => '\d+'], methods: ['GET'])]
    public function one(int $subscription_id): JsonResponse
    {
        $subscription = $this->subscriptionRepository->find($subscription_id);
        if (null === $subscription) {
            return $this->json(sprintf('Not Found'), 404);
        }

        return $this->json($subscription);
    }
}