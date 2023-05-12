<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscription;
use App\Entity\UserSubscriptionStatus;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = $this->createUser('victor', 'victor@test.com', '!ChangeMe!');
        $subscription = $this->createSubscription('Some Subscription', 'Some Description', 1, 100);
        $anotherSubscription = $this->createSubscription('Another Subscription', 'Another Description', 2, 200);

        $manager->persist($user);
        $manager->persist($subscription);
        $manager->persist($anotherSubscription);

        $userSubscription = $this->createUserSubscription(
            $user,
            $subscription,
            UserSubscriptionStatus::ACTIVE,
            new DateTimeImmutable('2023-01-01'),
            new DateTimeImmutable('2023-01-31')
        );
        $manager->persist($userSubscription);
        $manager->flush();
    }

    private function createUserSubscription(
        User $user,
        Subscription $subscription,
        UserSubscriptionStatus $userSubscriptionStatus,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): UserSubscription {
        $userSubscription = new UserSubscription();
        $userSubscription->setUser($user);
        $userSubscription->setSubscription($subscription);
        $userSubscription->setStatus($userSubscriptionStatus);
        $userSubscription->setStartDate($startDate);
        $userSubscription->setEndDate($endDate);

        return $userSubscription;
    }

    private function createSubscription(string $name, string $description, int $duration, float $price): Subscription
    {
        $subscription = new Subscription();
        $subscription->setName($name);
        $subscription->setDescription($description);
        $subscription->setDuration($duration);
        $subscription->setPrice($price);

        return $subscription;
    }

    private function createUser(string $name, string $email, string $plainPassword): User
    {
        $user = new User();

        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        return $user;
    }
}
