<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Exception;

class YousignException extends \Exception
{
    /**
     * @var array<string|int, mixed>
     */
    private array $errors;

    /**
     * @param array<string|int, mixed> $errors
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $errors = [])
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
