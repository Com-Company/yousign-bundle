<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Exception\YousignException;
use ComCompany\YousignBundle\Service\WebhookParserInterface;
use Symfony\Component\HttpFoundation\Request;

class WebhookParser implements WebhookParserInterface
{
    public function support(Request $request): bool
    {
        $version = $request->attributes->get('_route_params')['version'] ?? null;

        return 'v3' === $version;
    }

    public function parse(Request $request): WebhookPayload
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!($data['data']['signature_request'] ?? false) && !($data['data']['identity_document'] ?? false)) {
            throw new YousignException('Invalid payload', 0, null, $data);
        }

        $id = $data['data']['signature_request']['id'] ?? $data['data']['identity_document']['id'] ?? null;
        if (!($id ?? false)) {
            throw new YousignException('signature_request[\'id\'] or identity_document[\'id\'] not found', 0, null, $data);
        }
        $requestData = $data['data']['signature_request'] ?? $data['data']['identity_document'] ?? null;

        $time = null;
        if ($data['event_time'] ?? false) {
            $time = new \DateTime();
            $time->setTimestamp($data['event_time']);
        }

        return new WebhookPayload(
            $requestData['id'],
            $data['event_name'] ?? '',
            $requestData['status'] ?? '',
            $requestData['signers'] ?? [],
            $requestData['documents'] ?? [],
            $requestData['workspace_id'] ?? null,
            $requestData['external_id'] ?? null,
            $data['data']['reason'] ?? null,
            $time,
            $data['data']['signer'] ?? null,
            $data
        );
    }

    public function getEventName(Request $request): string
    {
        return (string) $request->toArray()['event_name'];
    }
}
