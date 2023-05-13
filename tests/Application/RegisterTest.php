<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Repository\UserRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private const TEST_EMAIL = 'register@test.com';
    private const TEST_REGISTER = [
        'email' => self::TEST_EMAIL,
        'name' => 'Register Name',
        'password' => '!ChangeMe!',
    ];

    private UserRepository $userRepository;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testRegisterSuccess(): void
    {
        $this->assertNull($this->userRepository->findOneBy(['email' => self::TEST_EMAIL]));

        $this->client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            [],
            json_encode(self::TEST_REGISTER, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful();
        $this->assertNotNull($this->userRepository->findOneBy(['email' => self::TEST_EMAIL]));
    }

    /**
     * @dataProvider invalidRequestContentDataProvider
     */
    public function testRegisterWithInvalidData(string $requestContent): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [], $requestContent);

        $this->assertResponseStatusCodeSame(400);
    }

    public function invalidRequestContentDataProvider(): \Generator
    {
        yield 'with empty name' => [
            'requestContent' => json_encode(['name' => '', 'email' => 'email@test.com', 'password' => '!ChangeMe!']),
        ];

        yield 'with invalid email' => [
            'requestContent' => json_encode(['name' => 'Some Name', 'email' => 'invalid email', 'password' => '!ChangeMe!']),
        ];

        yield 'with existing email' => [
            'requestContent' => json_encode(['name' => 'Some Name', 'email' => 'victor@test.com', 'password' => '!ChangeMe!']),
        ];
    }
}
