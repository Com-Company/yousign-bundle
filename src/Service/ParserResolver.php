<?php

namespace ComCompany\YousignBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class ParserResolver
{
    /** @var iterable<WebhookParserInterface> */
    private iterable $parsers;

    public function __construct(iterable $webhookParsers)
    {
        $this->parsers = $webhookParsers;
    }

    public function resolve(Request $request)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->support($request)) {
                return $parser->parse($request);
            }
        }

        return null;
    }
}
