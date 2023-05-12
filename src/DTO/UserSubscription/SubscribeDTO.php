<?php

declare(strict_types=1);

namespace App\DTO\UserSubscription;

use App\DTO\CheckRequestHasParam;
use App\DTO\RequestContentDecoder;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class SubscribeDTO
{
    use CheckRequestHasParam;
    use RequestContentDecoder;

    public function __construct(
        public readonly int $subscriptionId,
        public readonly User $user,
        public readonly \DateTimeImmutable $startDate,
        #[Assert\GreaterThanOrEqual(propertyPath: 'startDate')]
        public readonly \DateTimeImmutable $endDate
    ) {
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public static function fromRequestContent(int $subscriptionId, User $user, string $requestContent): self
    {
        $requestContentAsArray = self::requestContentToArray($requestContent);
        self::checkRequestHasParam($requestContentAsArray, ['startDate', 'endDate']);

        return new self(
            $subscriptionId,
            $user,
            new \DateTimeImmutable($requestContentAsArray['startDate']),
            new \DateTimeImmutable($requestContentAsArray['endDate'])
        );
    }
}
