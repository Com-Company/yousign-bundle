<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\WebhookPayload;
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

    public function parse(Request $request): ?WebhookPayload
    {
        $data = json_decode((string) $request->getContent(), true);
        if (!($data['data']['signature_request'] ?? false)) {
            return null; // todo exception
        }

        $payload = new WebhookPayload(
            $data['event_id'],
            $data['event_name'],
            $data['data']['signature_request']['status'] ?? '',
            $data['data']['signature_request']['signers'] ?? [],
            $data['data']['signature_request']['files'] ?? [],
            $data['data']['signature_request']['workspace_id'] ?? null,
        );

        return $payload;
    }
}
