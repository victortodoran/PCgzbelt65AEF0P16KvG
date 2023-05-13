<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ClientAuthenticator
{
    protected function authenticateClient(KernelBrowser $client, string $username = 'victor@test.com', string $password = '!ChangeMe!'): void
    {
        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ], JSON_THROW_ON_ERROR)
        );

        $responseContent = $client->getResponse()->getContent();
        if (false === $responseContent) {
            throw new \RuntimeException('Can not authenticate user. Login response is false');
        }

        /** @var string[] $data */
        $data = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
    }
}
