<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Service\Utils;

use ComCompany\YousignBundle\Exception\ClientException;

final class DateUtils
{
    /** @throws ClientException*/
    public static function toDatetime(string $date): \DateTime
    {
        $result = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $date);

        if (!$result) {
            throw new ClientException('Invalid date format :'.$date);
        }

        return $result;
    }
}
