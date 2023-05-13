<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\CheckRequestHasParam;
use App\DTO\RequestContentDecoder;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentDTO
{
    use CheckRequestHasParam;
    use RequestContentDecoder;

    public function __construct(
        #[Assert\NotBlank(message: 'The \'cardHolderName\' can not be blank.')]
        public readonly string $cardHolderName,
        #[Assert\NotBlank(message: 'The \'cardNumber\' can not be blank.')]
        public readonly string $cardNumber,
        #[Assert\NotBlank(message: 'The \'cvv\' can not be blank.')]
        public readonly string $cvv,
        #[Assert\GreaterThan(value: 0, message: 'The payment value must be greater than 0')]
        public readonly float $value
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function createFromRequestContent(string $requestContent): self
    {
        $requestContentAsArray = self::requestContentToArray($requestContent);
        self::checkRequestHasParam($requestContentAsArray, ['cardHolderName', 'cardNumber', 'cvv', 'value']);

        return new self(
            $requestContentAsArray['cardHolderName'],
            $requestContentAsArray['cardNumber'],
            $requestContentAsArray['ccv'],
            (float) $requestContentAsArray['value']
        );
    }
}
