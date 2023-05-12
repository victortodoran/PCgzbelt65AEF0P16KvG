<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ViolationListMessageExtractor
{
    protected function extractViolationMessages(ConstraintViolationListInterface $constraintViolationList): string
    {
        $violationMessage = '';
        /** @var ConstraintViolationInterface $constraintViolation */
        foreach ($constraintViolationList as $constraintViolation) {
            $violationMessage .= $constraintViolation->getMessage();
        }

        return $violationMessage;
    }
}