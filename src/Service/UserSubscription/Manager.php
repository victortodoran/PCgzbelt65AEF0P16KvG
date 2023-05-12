<?php

declare(strict_types=1);

namespace App\Service\UserSubscription;

use App\DTO\UserSubscription\SubscribeDTO;
use App\Entity\User;
use App\Entity\UserSubscription;
use App\Entity\UserSubscriptionStatus;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\SubscriptionRepository;
use App\Repository\UserSubscriptionRepository;
use App\Service\ViolationListMessageExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Manager
{
    use ViolationListMessageExtractor;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserSubscriptionRepository $userSubscriptionRepository,
        private readonly SubscriptionRepository $subscriptionRepository
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function subscribe(SubscribeDTO $subscribeDTO): UserSubscription
    {
        $constraintViolationList = $this->validator->validate($subscribeDTO);
        if ($constraintViolationList->count() > 0) {
            throw new ValidationException($this->extractViolationMessages($constraintViolationList));
        }

        $subscription = $this->subscriptionRepository->find($subscribeDTO->subscriptionId);
        if (null === $subscription) {
            throw new NotFoundException('Subscription not found');
        }

        $existingUserSubscription = $this->userSubscriptionRepository->findExistingActiveUserSubscription(
            $subscribeDTO->user,
            $subscription
        );
        $existingUserSubscription?->setStatus(UserSubscriptionStatus::CANCELED);

        $newUserSubscription = new UserSubscription();
        $newUserSubscription->setUser($subscribeDTO->user);
        $newUserSubscription->setSubscription($subscription);
        $newUserSubscription->setStartDate($subscribeDTO->startDate);
        $newUserSubscription->setEndDate($subscribeDTO->endDate);

        $this->entityManager->persist($newUserSubscription);
        $this->entityManager->flush();

        return $newUserSubscription;
    }

    /**
     * @throws NotFoundException
     */
    public function unsubscribe(User $user, int $subscriptionId): ?UserSubscription
    {
        $subscription = $this->subscriptionRepository->find($subscriptionId);
        if (null === $subscription) {
            throw new NotFoundException('Subscription not found');
        }

        $userSubscription = $this->userSubscriptionRepository->findExistingActiveUserSubscription(
            $user,
            $subscription
        );

        if (null === $userSubscription) {
            return null;
        }

        $userSubscription->setStatus(UserSubscriptionStatus::CANCELED);
        $this->entityManager->flush();

        return $userSubscription;
    }
}
