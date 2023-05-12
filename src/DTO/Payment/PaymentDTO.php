<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\CheckRequestHasParam;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentDTO
{
    use CheckRequestHasParam;

    public function __construct(
        #[Assert\NotBlank(message: 'The \'cardHolderName\' can not be blank.')]
        public readonly string $cardHolderName,
        #[Assert\NotBlank(message: 'The \'cardNumber\' can not be blank.')]
        public readonly string $cardNumber,
        #[Assert\NotBlank(message: 'The \'ccv\' can not be blank.')]
        public readonly string $ccv
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function createFromRequestContent(string $requestContent): self
    {
        $requestContentAsArray = json_decode($requestContent, true, 512, JSON_THROW_ON_ERROR);
        self::checkRequestHasParam($requestContentAsArray, ['cardHolderName', 'cardNumber', 'ccv']);

        return new self(
            $requestContentAsArray['cardHolderName'],
            $requestContentAsArray['cardNumber'],
            $requestContentAsArray['ccv']
        );
    }
}
