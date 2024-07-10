<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Exception;

class ApiException extends \Exception
{
    /**
     * @param string $message message
     * @param int    $code    error code
     */
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct('API Error: '.$message, $code, $previous);
    }
}
