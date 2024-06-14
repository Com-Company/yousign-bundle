<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use Symfony\Component\HttpFoundation\Request;

interface WebhookParserInterface
{
    public function support(Request $request): bool;

    public function parse(Request $request): ?WebhookPayload;
}
