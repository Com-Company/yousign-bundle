<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use App\Service\Supplier\Api\Advenis\Subscription\Initial\Bulletin;
use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\DTO\YousignV3\ProcedureDTO;
use ComCompany\YousignBundle\DTO\YousignV3\WebhookManagerInterface;
use ComCompany\YousignBundle\Service\EventHandlerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class WebhookManager
{
    /**
     * @var array<string, EventHandlerInterface>
     */
    private array $eventHandlers = [];


    public function handle(Request $request): void
    {
        $data = json_decode($request->getContent(), true);
        if (!($data['data']['signature_request'] ?? false)) {
            return; //todo exception
        }

        $payload = new WebhookPayload(
            $data['event_id'],
            $data['event_name'],
            $data['data']['signature_request']['status'] ?? '',
            $data['data']['signature_request']['signers'] ?? [],
            $data['data']['signature_request']['files'] ?? [],
            $data['data']['signature_request']['workspace_id'] ?? null,
        );

        $this->eventHandlers[$data['event_name']]->handle($payload);
    }

    public function addEventHandler(string $event, $eventHandler) {
        $this->eventHandlers[$event] = $eventHandler;
    }
}
