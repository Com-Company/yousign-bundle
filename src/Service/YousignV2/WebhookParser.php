<?php

namespace ComCompany\YousignBundle\Service\YousignV2;

use ComCompany\YousignBundle\DTO\WebhookPayload;
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

    public function parse(Request $request): ?WebhookPayload
    {
        $data = json_decode($request->getContent(), true);
        if (!($data['data']['procedure'] ?? false)) {
            return null; // todo exception
        }

        $workspace = ($data['procedure']['workspace'] ?? false)
            ? str_replace('/workspaces/', '', $data['procedure']['workspace'] ?? '')
            : null;

        $payload = new WebhookPayload(
            '',
            $data['eventName'],
            $data['procedure']['status'] ?? '',
            $data['procedure']['members'] ?? [],
            $data['procedure']['files'] ?? [],
            $workspace,
        );

        return $payload;
    }
}
