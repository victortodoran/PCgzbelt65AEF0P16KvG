<?php

declare(strict_types=1);

namespace App\Controller\Api\UserSubscriptions;

use App\DTO\UserSubscription\SubscribeDTO;
use App\Repository\UserSubscriptionRepository;
use App\Service\UserSubscription\Manager as UserSubscriptionManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserSubscription extends AbstractController
{
    public function __construct(
        private readonly UserSubscriptionRepository $userSubscriptionRepository,
        private readonly UserSubscriptionManager $userSubscriptionManager
    ) {}

    #[Route('/api/subscriptions/{subscription_id}/subscribe', name: 'app_user_subscriptions_subscribe', methods: ['POST'])]
    public function subscribe(int $subscription_id, Request $request): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json('Forbidden', 403);
        }

        try {
            $this->userSubscriptionManager->subscribe(
                SubscribeDTO::fromRequestContent($subscription_id, $user, $request->getContent())
            );
        } catch (Exception) {
            return $this->json('Bad Request', 400);
        }

        return $this->json('Success');
    }

    #[Route('/api/subscriptions/{subscription_id}/unsubscribe', name: 'app_user_subscriptions_unsubscribe', methods: ['POST'])]
    public function unsubscribe(int $subscription_id, Request $request): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json('Forbidden', 403);
        }

        try {
            $this->userSubscriptionManager->unsubscribe($user, $subscription_id);
        } catch (Exception) {
            return $this->json('Bad Request', 400);
        }

        return $this->json('Success');
    }

    #[Route('/api/subscriptions/me', name: 'app_user_subscriptions_all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json('Forbidden', 403);
        }

        return $this->json(
            $this->userSubscriptionRepository->findBy(['user' => $user])
        );
    }
}