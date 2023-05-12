<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait CheckRequestHasParam
{
    /**
     * @param array<string, string> $requestContentAsArray
     * @param string[]              $requiredParams
     *
     * @throws BadRequestHttpException
     */
    protected static function checkRequestHasParam(array $requestContentAsArray, array $requiredParams): void
    {
        foreach ($requiredParams as $requiredParamName) {
            if (!isset($requestContentAsArray[$requiredParamName])) {
                throw new BadRequestHttpException(sprintf('%s is required and is missing from Request', $requiredParamName));
            }
        }
    }
}
