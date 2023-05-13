<?php

declare(strict_types=1);

namespace App\Tests\Application\UserSubscriptions;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscriptionStatus;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Repository\UserSubscriptionRepository;
use App\Tests\Application\ClientAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnsubscribeTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use ClientAuthenticator;

    private KernelBrowser $client;
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;
    private UserSubscriptionRepository $userSubscriptionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->userSubscriptionRepository = self::getContainer()->get(UserSubscriptionRepository::class);
        $this->subscriptionRepository = self::getContainer()->get(SubscriptionRepository::class);
    }

    public function testUnsubscribeWithUnauthenticatedUser(): void
    {
        $this->client->request('POST', '/api/subscriptions/1/unsubscribe');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testUnsubscribe(): void
    {
        $user = $this->findUserByEmail('victor@test.com');
        $subscription = $this->findSubscriptionById(1);

        $this->assertNotNull($this->userSubscriptionRepository->findOneBy(
            ['user' => $user, 'subscription' => $subscription])
        );

        $this->authenticateClient($this->client);
        $this->client->request('POST', '/api/subscriptions/1/unsubscribe');

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->userSubscriptionRepository->findOneBy(
            ['user' => $user, 'subscription' => $subscription, 'status' => UserSubscriptionStatus::ACTIVE])
        );
    }

    private function findUserByEmail(string $email): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            $this->fail(sprintf('This test expects user with email %s to exist', $email));
        }

        return $user;
    }

    private function findSubscriptionById(int $id): Subscription
    {
        $subscription = $this->subscriptionRepository->find($id);
        if (null === $subscription) {
            $this->fail(sprintf('This test expects that subscription with id %d exists', $id));
        }

        return $subscription;
    }
}
