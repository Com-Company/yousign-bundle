<?php

namespace ComCompany\YousignBundle\Service;

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

    public function resolve(Request $request): ?WebhookParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->support($request)) {
                return $parser;
            }
        }

        return null;
    }
}
