<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\DTO\Payment\PaymentDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Processor
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Dummy Payment Processor
     * ccv starts with 1 -> payment successful
     * ccv does not start with 1 -> payment failed.
     */
    public function processPayment(PaymentDTO $paymentDTO): bool
    {
        $constraintViolation = $this->validator->validate($paymentDTO);
        if ($constraintViolation->count()) {
            return false;
        }

        return str_starts_with($paymentDTO->cvv, '1');
    }
}
