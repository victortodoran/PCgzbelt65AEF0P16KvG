<?php

declare(strict_types=1);

namespace App\Tests\Application\UserSubscriptions;

use App\DTO\UserSubscription\SubscribeDTO;
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
use Symfony\Component\Serializer\SerializerInterface;

class SubscribeTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use ClientAuthenticator;

    private KernelBrowser $client;
    private SerializerInterface $serializer;
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;
    private UserSubscriptionRepository $userSubscriptionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->serializer = self::getContainer()->get(SerializerInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->userSubscriptionRepository = self::getContainer()->get(UserSubscriptionRepository::class);
        $this->subscriptionRepository = self::getContainer()->get(SubscriptionRepository::class);
    }

    public function testSubscribeWithUnauthenticatedUser(): void
    {
        $subscribeDTO = $this->createSubscribeDTO(2);
        $this->client->request(
            'POST', '/api/subscriptions/2/subscribe', [], [], [], $this->serializer->serialize($subscribeDTO, 'json')
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testSubscribeToNewSubscription(): void
    {
        $user = $this->findUserByEmail('victor@test.com');
        $subscription = $this->findSubscriptionById(2);
        $this->assertNull($this->userSubscriptionRepository->findOneBy(['user' => $user, 'subscription' => $subscription]));

        $subscribeDTO = $this->createSubscribeDTO(2);
        $this->authenticateClient($this->client);
        $this->client->request(
            'POST', '/api/subscriptions/2/subscribe', [], [], [], $this->serializer->serialize($subscribeDTO, 'json')
        );

        $this->assertResponseIsSuccessful();
        $this->assertNotNull($this->userSubscriptionRepository->findOneBy(['user' => $user, 'subscription' => $subscription]));
    }

    public function testSubscribeToExistingSubscriptionCancelsExistingSubscription(): void
    {
        $user = $this->findUserByEmail('victor@test.com');
        $subscription = $this->findSubscriptionById(1);
        $existingSubscription = $this->userSubscriptionRepository->findOneBy(
            ['user' => $user, 'subscription' => $subscription, 'status' => UserSubscriptionStatus::ACTIVE]
        );
        if (null === $existingSubscription) {
            $this->fail('Test expects for it to be an existing user subscription');
        }

        $subscribeDTO = $this->createSubscribeDTO(1);
        $this->authenticateClient($this->client);
        $this->client->request(
            'POST', '/api/subscriptions/1/subscribe', [], [], [], $this->serializer->serialize($subscribeDTO, 'json')
        );

        $this->assertResponseIsSuccessful();

        $existingSubscription = $this->userSubscriptionRepository->find($existingSubscription->getId());
        $this->assertSame(UserSubscriptionStatus::CANCELED, $existingSubscription?->getStatus());
    }

    private function createSubscribeDTO(int $subscriptionId): SubscribeDTO
    {
        return new SubscribeDTO(
            $subscriptionId,
            $this->createMock(User::class),
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-01-31')
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
