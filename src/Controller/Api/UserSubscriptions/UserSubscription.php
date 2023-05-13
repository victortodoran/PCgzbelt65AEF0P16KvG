<?php

declare(strict_types=1);

namespace App\Controller\Api\UserSubscriptions;

use App\DTO\UserSubscription\SubscribeDTO;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\UserSubscriptionRepository;
use App\Service\UserSubscription\Manager as UserSubscriptionManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserSubscription extends AbstractController
{
    public function __construct(
        private readonly UserSubscriptionRepository $userSubscriptionRepository,
        private readonly UserSubscriptionManager $userSubscriptionManager
    ) {
    }

    /**
     * Subscribe to a subscription.
     *
     * IMPORTANT: If there is an already active UserSubscription it will be cancelled
     */
    #[Route('/api/subscriptions/{subscription_id}/subscribe', name: 'app_user_subscriptions_subscribe', methods: ['POST'])]
    #[OA\Tag(name: 'User Subscriptions')]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: SubscribeDTO::class)))]
    #[OA\Response(
        response: 200,
        description: 'Successfully Subscribed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 200),
                new OA\Property(property: 'message', example: 'Success'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorised Response',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 401),
                new OA\Property(property: 'message', example: 'Invalid JWT token'),
            ]
        )
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
    #[OA\Response(
        response: 500,
        description: 'User is not available',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 500),
                new OA\Property(property: 'message', example: 'User is not available'),
            ]
        )
    )]
    #[Security(name: 'BearerAuth')]
    public function subscribe(int $subscription_id, Request $request): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json(['message' => 'User is not available', 'code' => 500], 500);
        }

        try {
            assert($user instanceof User);
            $this->userSubscriptionManager->subscribe(
                SubscribeDTO::fromRequestContent($subscription_id, $user, $request->getContent())
            );
        } catch (\JsonException|ValidationException) {
            return $this->json(['message' => 'Invalid JSON Or DateTime Values'], 400);
        } catch (NotFoundException) {
            return $this->json(['message' => 'Subscription Not Found', 'code' => 404], 404);
        }

        return $this->json('Success');
    }

    /**
     * Unsubscribe to a subscription.
     */
    #[Route('/api/subscriptions/{subscription_id}/unsubscribe', name: 'app_user_subscriptions_unsubscribe', methods: ['POST'])]
    #[OA\Tag(name: 'User Subscriptions')]
    #[OA\Response(
        response: 200,
        description: 'Successfully Unsubscribed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 200),
                new OA\Property(property: 'message', example: 'Success'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorised Response',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 401),
                new OA\Property(property: 'message', example: 'Invalid JWT token'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User Subscription Not Found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 404),
                new OA\Property(property: 'message', example: 'User Subscription Not Found'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'User is not available',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 500),
                new OA\Property(property: 'message', example: 'User is not available'),
            ]
        )
    )]
    #[Security(name: 'Bearer')]
    public function unsubscribe(int $subscription_id): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json(['message' => 'User is not available', 'code' => 500], 500);
        }

        try {
            assert($user instanceof User);
            $this->userSubscriptionManager->unsubscribe($user, $subscription_id);
        } catch (NotFoundException) {
            return $this->json(['message' => 'User Subscription Not Found', 'code' => 404], 404);
        }

        return $this->json(['message' => 'Success', 'code' => 200]);
    }

    /**
     * Show all subscriptions belonging to a particular customer.
     */
    #[Route('/api/subscriptions/me', name: 'app_user_subscriptions_all', methods: ['GET'])]
    #[OA\Tag(name: 'User Subscriptions')]
    #[OA\Response(
        response: 200,
        description: 'A list of all UserSubscriptions for a given User',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: \App\Entity\UserSubscription::class))
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorised Response',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 401),
                new OA\Property(property: 'message', example: 'Invalid JWT token'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'User is not available',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', example: 500),
                new OA\Property(property: 'message', example: 'User is not available'),
            ]
        )
    )]
    #[Security(name: 'Bearer')]
    public function all(): JsonResponse
    {
        if (null === $user = $this->getUser()) {
            return $this->json(['message' => 'User is not available', 'code' => 500], 500);
        }

        return $this->json(
            $this->userSubscriptionRepository->findBy(['user' => $user])
        );
    }
}
