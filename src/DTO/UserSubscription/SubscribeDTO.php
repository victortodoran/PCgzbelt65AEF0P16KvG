<?php

declare(strict_types=1);

namespace App\DTO\UserSubscription;

use App\DTO\CheckRequestHasParam;
use App\DTO\RequestContentDecoder;
use App\Entity\User;
use App\Exception\ValidationException;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class SubscribeDTO
{
    use CheckRequestHasParam;
    use RequestContentDecoder;

    public function __construct(
        public readonly int $subscriptionId,
        #[Ignore]
        public readonly User $user,
        public readonly \DateTimeImmutable $startDate,
        #[Assert\GreaterThanOrEqual(propertyPath: 'startDate')]
        public readonly \DateTimeImmutable $endDate
    ) {
    }

    /**
     * @throws \JsonException
     * @throws ValidationException
     */
    public static function fromRequestContent(int $subscriptionId, User $user, string $requestContent): self
    {
        $requestContentAsArray = self::requestContentToArray($requestContent);
        self::checkRequestHasParam($requestContentAsArray, ['startDate', 'endDate']);

        try {
            return new self(
                $subscriptionId,
                $user,
                new \DateTimeImmutable($requestContentAsArray['startDate']),
                new \DateTimeImmutable($requestContentAsArray['endDate'])
            );
        } catch (\Exception $e) {
            throw new ValidationException($e->getMessage(), 0, $e);
        }
    }
}
