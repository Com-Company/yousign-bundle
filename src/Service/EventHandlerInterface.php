<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Exception\YousignException;

interface EventHandlerInterface
{
    public function handle(WebhookPayload $payload): void;

    public function onError(YousignException $e): void;
}
