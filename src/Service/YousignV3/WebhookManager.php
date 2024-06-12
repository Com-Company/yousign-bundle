<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\DTO\YousignV3\ProcedureDTO;
use ComCompany\YousignBundle\DTO\YousignV3\WebhookManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class WebhookManager
{
//    private ?string $uri = null;
//
//    private ?WebhookManagerInterface $manager = null;
//
//    public function setUri(?string $uri): void
//    {
//        $this->uri = $uri;
//    }
//
//    public function setManager(?WebhookManagerInterface $manager): void
//    {
//        $this->manager = $manager;
//    }

    public function handle(Request $request): void
    {
        $data = json_decode($request->getContent(), true);
        if (!($data['signature_request'] ?? false)) {
            return; //todo exception
        }

        $payload = new WebhookPayload(
            $data['event_id'],
            $data['event_name'],
            $data['signature_request']['status'] ?? '',
            $data['signature_request']['signers'] ?? [],
            $data['signature_request']['files'] ?? [],
            $data['signature_request']['workspace_id'] ?? null,
        );

    }
}
