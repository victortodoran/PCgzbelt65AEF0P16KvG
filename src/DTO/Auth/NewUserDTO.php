<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\DTO\CheckRequestHasParam;
use Symfony\Component\Validator\Constraints as Assert;

class NewUserDTO
{
    use CheckRequestHasParam;

    public function __construct(
        #[Assert\NotBlank(message: 'The \'name\' can not be blank.')]
        public readonly string $name,
        #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
        public readonly string $email,
        #[Assert\NotBlank(message: 'The \'password\' can not be blank.')]
        public readonly string $password
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function fromRequestContent(string $requestContent): self
    {
        $requestContentAsArray = json_decode($requestContent, true, 512, JSON_THROW_ON_ERROR);
        self::checkRequestHasParam($requestContentAsArray, ['email', 'name', 'password']);

        return new self(
            $requestContentAsArray['name'],
            $requestContentAsArray['email'],
            $requestContentAsArray['password']
        );
    }
}
