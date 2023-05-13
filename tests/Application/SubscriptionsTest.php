<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SubscriptionsTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testGetAllSubscriptionsSuccess(): void
    {
        $this->client->request('GET', '/api/subscriptions');
        $this->assertResponseIsSuccessful();
        // TODO For completion contents of $this->client->getResponse()
    }

    public function testGetSubscriptionByIdSuccess(): void
    {
        $this->client->request('GET', 'api/subscriptions/1');
        $this->assertResponseIsSuccessful();
        // TODO For completion contents of $this->client->getResponse()
    }

    public function testGetSubscriptionByIdFail(): void
    {
        $this->client->request('GET', 'api/subscriptions/1000');
        $this->assertResponseStatusCodeSame(404);
    }
}
