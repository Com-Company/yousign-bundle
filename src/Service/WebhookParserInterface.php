<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Exception\YousignException;
use Symfony\Component\HttpFoundation\Request;

interface WebhookParserInterface
{
    public function support(Request $request): bool;

    /** @throws YousignException */
    public function parse(Request $request): WebhookPayload;

    public function getEventName(Request $request): string;
}
