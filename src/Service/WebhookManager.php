<?php

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\Exception\YousignException;
use Symfony\Component\HttpFoundation\Request;

class WebhookManager
{
    /**
     * @var array<string, EventHandlerInterface>
     */
    private array $eventHandlers = [];

    private ?EventHandlerInterface $defaultHandler = null;

    private ParserResolver $resolver;

    public function __construct(ParserResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(Request $request): void
    {
        $parser = $this->resolver->resolve($request);
        if (!$parser) {
            return; // todo log
        }

        $handler = $this->eventHandlers[$parser->getEventName($request)] ?? $this->defaultHandler;
        if (!$handler) {
            return; // todo logs
        }
        try {
            $payload = $parser->parse($request);
        } catch (YousignException $e) {
            $handler->onError($e);

            return;
        }

        $handler->handle($payload);
    }

    public function addEventHandler(string $event, EventHandlerInterface $eventHandler): void
    {
        $this->eventHandlers[$event] = $eventHandler;
    }

    public function setDefaultHandler(EventHandlerInterface $defaultHandler): void
    {
        $this->defaultHandler = $defaultHandler;
    }
}
