<?php

declare(strict_types=1);

namespace App\Tests\Application\UserSubscriptions;

use App\Tests\Application\ClientAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetUserSubscriptionsTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use ClientAuthenticator;

    private KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testGetUserSubscriptionsWithUnauthenticatedClient(): void
    {
        $this->client->request('GET', '/api/subscriptions/me');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetUserSubscriptionsWithAuthenticatedClient(): void
    {
        $this->authenticateClient($this->client);
        $this->client->request('GET', '/api/subscriptions/me');
        $this->assertResponseIsSuccessful();
        // TODO For completion contents of $this->client->getResponse()
    }
}
