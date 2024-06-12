<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\SignatureContract\DTO\Document;
use ComCompany\SignatureContract\DTO\Fields;
use ComCompany\SignatureContract\DTO\Member;
use ComCompany\SignatureContract\DTO\MemberConfig;
use ComCompany\SignatureContract\DTO\ProcedureConfig;
use ComCompany\SignatureContract\Exception\ApiException;
use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\SignatureContract\Response\SignatureResponse;
use ComCompany\SignatureContract\Service\SignatureContractInterface;
use ComCompany\YousignBundle\DTO\WebhookPayload;
use ComCompany\YousignBundle\Service\WebhookParserInterface;
use Safe\Exceptions\StringsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;
use function Safe\sprintf;

class WebhookParser implements WebhookParserInterface
{
    public function support(Request $request): bool {
        $version = $request->attributes->get('_route_params')['version'] ?? null;
        return $version === 'v3';
    }

    public function parse(Request $request): ?WebhookPayload
    {
        $data = json_decode($request->getContent(), true);
        if (!($data['data']['signature_request'] ?? false)) {
            return null; //todo exception
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