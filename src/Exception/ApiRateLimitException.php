<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Exception;

class ApiRateLimitException extends ApiException
{
    /**
     * @param string $message message
     * @param int    $code    error code
     */
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null, array $errors = [])
    {
        parent::__construct($message, $code, $previous, $errors);
    }
}
