<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Exception\YousignException;
use ComCompany\YousignBundle\Service\WebhookParserInterface;
use Symfony\Component\HttpFoundation\Request;

class WebhookBankAccountParser implements WebhookParserInterface
{
    public function support(Request $request): bool
    {
        $version = $request->attributes->get('_route_params')['version'] ?? null;

        return 'v3' === $version && $this->getEventName($request) === 'verification.bank_account_lookup.done';
    }

    public function parse(Request $request): WebhookPayload
    {
        $data = json_decode((string) $request->getContent(), true);

        if (!($data['data']['bank_account_lookup'] ?? false)) {
            throw new YousignException('Invalid payload', 0, null, $data);
        }

        $requestData = $data['data']['bank_account_lookup'];

        $id = $requestData['id'] ?? null;
        if (!($id ?? false)) {
            throw new YousignException('bank_account_lookup[\'id\'] not found', 0, null, $data);
        }

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
            $data,
            $requestData['extracted_from_document']['iban'] ?? null,
            $requestData['status_codes'] ?? null,
        );
    }

    public function getEventName(Request $request): string
    {
        return (string) $request->toArray()['event_name'];
    }
}
