<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\DTO\Auth\NewUserDTO;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Service\Auth\Registrant;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrantTest extends TestCase
{
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private EntityManagerInterface&MockObject $entityManager;
    private ValidatorInterface&MockObject $validator;
    private MockObject&ConstraintViolationListInterface $constraintViolationList;
    private UserRepository&MockObject $userRepository;

    private Registrant $registrant;
    private NewUserDTO $newUserDTO;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->validator->method('validate')->willReturn($this->constraintViolationList);

        $this->userRepository = $this->createMock(UserRepository::class);

        $this->newUserDTO = new NewUserDTO('victor', 'victor@test.com', 'SuperSecure');

        $this->registrant = new Registrant(
            $this->passwordHasher,
            $this->entityManager,
            $this->validator,
            $this->userRepository
        );
    }

    public function testRegisterUserWithInvalidNewUserDto(): void
    {
        $this->constraintViolationList->method('count')->willReturn(1);

        $this->passwordHasher->expects($this->never())->method('hashPassword');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(ValidationException::class);
        $this->registrant->registerUser($this->newUserDTO);
    }

    public function testRegisterUserWithAlreadyUsedEmail(): void
    {
        $this->userRepository->method('findOneBy')->willReturn($this->createMock(User::class));

        $this->passwordHasher->expects($this->never())->method('hashPassword');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->expectException(ValidationException::class);
        $this->registrant->registerUser($this->newUserDTO);
    }

    public function testRegisterUserHappyPath(): void
    {
        $this->passwordHasher->expects($this->once())->method('hashPassword');
        $this->entityManager->expects($this->atLeastOnce())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->registrant->registerUser($this->newUserDTO);
    }
}
