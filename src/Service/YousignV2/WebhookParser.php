<?php

namespace ComCompany\YousignBundle\Service\YousignV2;

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

        return 'v2' === $version;
    }

    public function parse(Request $request): WebhookPayload
    {
        $data = json_decode((string) $request->getContent(), true);
        if (!($data['data']['procedure'] ?? false)) {
            throw new YousignException('Invalid payload', 0, null, $data);
        }

        $procedure = $data['data']['procedure'];
        if (!($procedure['id'] ?? false)) {
            throw new YousignException('Signature id not found', 0, null, $data);
        }
        $workspace = ($procedure['workspace'] ?? false)
            ? str_replace('/workspaces/', '', $procedure['workspace'] ?? '')
            : null;

        $payload = new WebhookPayload(
            $procedure['id'],
            $data['eventName'],
            $procedure['status'] ?? '',
            $procedure['members'] ?? [],
            $procedure['files'] ?? [],
            is_string($workspace) ? $workspace : null,
        );

        return $payload;
    }

    public function getEventName(Request $request): string
    {
        return (string) $request->request->get('eventName');
    }
}
