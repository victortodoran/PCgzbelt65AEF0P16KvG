<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UserSubscription;

use App\DTO\UserSubscription\SubscribeDTO;
use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscription;
use App\Entity\UserSubscriptionStatus;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\SubscriptionRepository;
use App\Repository\UserSubscriptionRepository;
use App\Service\UserSubscription\Manager as UserSubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ManagerTest extends TestCase
{
    private ValidatorInterface&MockObject $validator;
    private ConstraintViolationListInterface&MockObject $constraintViolationList;
    private EntityManagerInterface&MockObject $entityManager;
    private UserSubscriptionRepository&MockObject $userSubscriptionRepository;
    private SubscriptionRepository&MockObject $subscriptionRepository;
    private UserSubscriptionManager $userSubscriptionManager;
    private SubscribeDTO $subscribeDTO;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->method('validate')->willReturn($this->constraintViolationList);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userSubscriptionRepository = $this->createMock(UserSubscriptionRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);

        $this->subscribeDTO = new SubscribeDTO(
            1,
            $this->createMock(User::class),
            $this->createMock(\DateTimeImmutable::class),
            $this->createMock(\DateTimeImmutable::class)
        );

        $this->userSubscriptionManager = new UserSubscriptionManager(
            $this->validator,
            $this->entityManager,
            $this->userSubscriptionRepository,
            $this->subscriptionRepository
        );
    }

    public function testSubscribeThrowsExceptionWithInvalidInput(): void
    {
        $this->constraintViolationList->method('count')->willReturn(1);

        $this->subscriptionRepository->expects($this->never())->method('find');
        $this->userSubscriptionRepository->expects($this->never())->method('findExistingActiveUserSubscription');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(ValidationException::class);
        $this->userSubscriptionManager->subscribe($this->subscribeDTO);
    }

    public function testSubscribeThrowsNotFoundExceptionWhenSubscriptionIsNotFound(): void
    {
        $this->userSubscriptionRepository->expects($this->never())->method('findExistingActiveUserSubscription');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(NotFoundException::class);
        $this->userSubscriptionManager->subscribe($this->subscribeDTO);
    }

    /**
     * @dataProvider subscribeDataProvider
     */
    public function testSubscribe(?UserSubscription $existingUserSubscription): void
    {
        $this->subscriptionRepository->method('find')
            ->willReturn($this->createMock(Subscription::class))
        ;
        $this->userSubscriptionRepository->method('findExistingActiveUserSubscription')
            ->willReturn($existingUserSubscription)
        ;

        $this->entityManager->expects($this->atLeastOnce())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->userSubscriptionManager->subscribe($this->subscribeDTO);
    }

    public function subscribeDataProvider(): \Generator
    {
        yield 'with no existing user subscription' => ['existingUserSubscription' => null];

        $existingUserSubscription = $this->createMock(UserSubscription::class);
        $existingUserSubscription->expects($this->once())
            ->method('setStatus')
            ->with(UserSubscriptionStatus::CANCELED)
        ;
        yield 'with existing user subscription' => ['existingUserSubscription' => $existingUserSubscription];
    }

    public function testUnsubscribeThrowsNotFoundExceptionWhenSubscriptionIsNotFound(): void
    {
        $this->entityManager->expects($this->never())->method('flush');
        $this->expectException(NotFoundException::class);

        $this->userSubscriptionManager->unsubscribe($this->createMock(User::class), 1);
    }

    public function testUnsubscribeReturnsNullWhenUserSubscriptionIsNotFound(): void
    {
        $this->entityManager->expects($this->never())->method('flush');
        $this->subscriptionRepository->method('find')->willReturn($this->createMock(Subscription::class));

        $this->assertNull(
            $this->userSubscriptionManager->unsubscribe(
                $this->createMock(User::class), 1
            )
        );
    }

    public function testUnsubscribe(): void
    {
        $this->entityManager->expects($this->once())->method('flush');
        $this->subscriptionRepository->method('find')->willReturn($this->createMock(Subscription::class));

        $userSubscription = $this->createMock(UserSubscription::class);
        $userSubscription->expects($this->once())->method('setStatus')->with(UserSubscriptionStatus::CANCELED);
        $this->userSubscriptionRepository->method('findExistingActiveUserSubscription')->willReturn($userSubscription);

        $this->assertSame(
            $userSubscription,
            $this->userSubscriptionManager->unsubscribe(
                $this->createMock(User::class), 1
            )
        );
    }
}
