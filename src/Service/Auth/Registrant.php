<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Auth\NewUserDTO;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Service\ViolationListMessageExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Registrant
{
    use ViolationListMessageExtractor;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * @throws ValidationException
     */
    public function registerUser(NewUserDTO $newUserDTO): void
    {
        $constraintViolationList = $this->validator->validate($newUserDTO);
        if ($constraintViolationList->count() > 0) {
            throw new ValidationException($this->extractViolationMessages($constraintViolationList));
        }

        $userWithGivenEmail = $this->userRepository->findOneBy(['email' => $newUserDTO->email]);
        if (null !== $userWithGivenEmail) {
            throw new ValidationException(sprintf('Given email %s is not available for a new user', $newUserDTO->email));
        }

        $newUserEntity = new User();
        $newUserEntity->setEmail($newUserDTO->email);
        $newUserEntity->setName($newUserDTO->name);
        $newUserEntity->setPassword(
            $this->passwordHasher->hashPassword(
                $newUserEntity,
                $newUserDTO->password
            )
        );

        $this->entityManager->persist($newUserEntity);
        $this->entityManager->flush();
    }
}