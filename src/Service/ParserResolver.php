<?php

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use Symfony\Component\HttpFoundation\Request;

class ParserResolver
{
    /** @var iterable<WebhookParserInterface> */
    private iterable $parsers;

    /** @param iterable<WebhookParserInterface> $webhookParsers */
    public function __construct(iterable $webhookParsers)
    {
        $this->parsers = $webhookParsers;
    }

    public function resolve(Request $request): ?WebhookPayload
    {
        foreach ($this->parsers as $parser) {
            if ($parser->support($request)) {
                return $parser->parse($request);
            }
        }

        return null;
    }
}
