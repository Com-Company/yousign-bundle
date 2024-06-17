<?php

namespace ComCompany\YousignBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class WebhookManager
{
    /**
     * @var array<string, EventHandlerInterface>
     */
    private array $eventHandlers = [];

    private ParserResolver $resolver;

    public function __construct(ParserResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(Request $request): void
    {
        $payload = $this->resolver->resolve($request);

        if (!$payload) {
            return; // todo exception
        }
        $this->eventHandlers[$payload->getEventName()]->handle($payload);
    }

    public function addEventHandler(string $event, $eventHandler)
    {
        $this->eventHandlers[$event] = $eventHandler;
    }
}
