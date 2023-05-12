<?php

declare(strict_types=1);

namespace App\DTO;

trait RequestContentDecoder
{
    /**
     * @return array<string, string>
     *
     * @throws \JsonException
     */
    public static function requestContentToArray(string $requestContent): array
    {
        $requestContentAsArray = json_decode($requestContent, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($requestContentAsArray)) {
            throw new \RuntimeException('Can not decode requestContent to array');
        }

        return $requestContentAsArray;
    }
}
