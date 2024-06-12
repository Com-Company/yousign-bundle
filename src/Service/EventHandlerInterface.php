<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\WebhookPayload;

interface EventHandlerInterface
{
    public function handle(WebhookPayload $payload): void;
}
