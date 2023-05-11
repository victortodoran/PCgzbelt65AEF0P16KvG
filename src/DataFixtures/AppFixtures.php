<?php

namespace App\DataFixtures;

use App\Entity\User;
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
        $this->addUsers($manager);
        $manager->flush();
    }

    private function addUsers(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Victor');
        $user->setEmail('victor@test.com');
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                '!ChangeMe!'
            )
        );

        $manager->persist($user);
    }
}
