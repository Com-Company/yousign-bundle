<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Exception\YousignException;
use ComCompany\YousignBundle\Service\WebhookParserInterface;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_decode;

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
        if (!($data['data']['signature_request'] ?? false)) {
            throw new YousignException('Invalid payload', 0, null, $data);
        }

        $signatureRequest = $data['data']['signature_request'];
        if (!($signatureRequest['id'] ?? false)) {
            throw new YousignException('signature_request[\'id\'] not found', 0, null, $data);
        }

        $payload = new WebhookPayload(
            $signatureRequest['id'],
            $data['event_name'] ?? '',
            $signatureRequest['status'] ?? '',
            $signatureRequest['signers'] ?? [],
            $signatureRequest['files'] ?? [],
            $signatureRequest['workspace_id'] ?? null,
        );

        return $payload;
    }

    public function getEventName(Request $request): string
    {
        return (string) $request->request->get('event_name');
    }
}
