<?php

namespace ComCompany\YousignBundle\Service\YousignV2;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Fields;
use ComCompany\YousignBundle\DTO\Location;
use ComCompany\YousignBundle\DTO\Member;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\Response\DocumentResponse;
use ComCompany\YousignBundle\DTO\Response\MemberResponse;
use ComCompany\YousignBundle\DTO\Response\SignatureResponse;
use ComCompany\YousignBundle\Exception\ApiException;
use ComCompany\YousignBundle\Exception\ClientException;
use ComCompany\YousignBundle\Service\ClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientYousign implements ClientInterface
{
    private HttpClientInterface $httpClient;

    private string $appUri;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /** @throws ClientException */
    public function start(Fields $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse
    {
        throw new ClientException("'start' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @throws ClientException */
    public function initiateProcedure(?ProcedureConfig $config = null): string
    {
        throw new ClientException("'initiateProcedure' method is no longer implemented for this Yousing v2.", 501);
    }

    /**
     * @return mixed[]
     *
     * @throws ClientException
     */
    public function sendSigner(string $procedureId, Member $member): array
    {
        throw new ClientException("'sendSigner' method is no longer implemented for this Yousing v2.", 501);
    }

    /**
     * @return array<string, string>
     *
     * @throws ClientException
     */
    public function sendFollower(string $procedureId, string $email, string $locale = 'fr'): array
    {
        throw new ClientException("'sendFollower' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @throws ClientException */
    public function sendDocument(string $procedureId, Document $document): string
    {
        throw new ClientException("'sendDocument' method is no longer implemented for this Yousing v2.", 501);
    }

    /**
     * @throws ClientException
     */
    public function getProcedure(string $procedureId): SignatureResponse
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $response = $this->request('GET', 'procedures/'.$procedureId);

        if (!is_array($response) || empty($response)) {
            throw new ApiException('Get procedure error');
        }
        $removePrefix = fn ($str) => substr($str, strrpos("/$str", '/') ?: 0);
        $signatureResponse = new SignatureResponse();
        $signatureResponse->setProcedureId($response['id']);
        $signatureResponse->setCreationDate($response['createdAt']);
        $signatureResponse->setExpirationDate($response['expiresAt']);
        $signatureResponse->setWorkspaceId($removePrefix($response['workspace_id']));

        foreach (($response['files'] ?? []) as $document) {
            $signatureResponse->addDocument(new DocumentResponse(null, $removePrefix($document['id']), $document['type']));
        }

        foreach (($response['members'] ?? []) as $member) {
            $signUri = ($this->appUri ?? '')."/procedure/sign?members={$member['id']}";
            $signatureResponse->addMember(new MemberResponse(null, $removePrefix($member['id']), $member['status'], $signUri));
        }

        return $signatureResponse;
    }

    public function activate(string $idProcedure): SignatureResponse
    {
        throw new ClientException("'activate' method is no longer implemented for this Yousing v2.", 501);
    }

    public function downloadDocument(string $procedureId, string $documentId): string
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        if (!$documentId) {
            throw new ClientException('documentId is required');
        }

        $response = $this->httpClient->request('GET', $documentId.'/download');

        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error Processing Request: '.$response->getContent(false), $response->getStatusCode());
        }

        return $response->getContent(false);
    }

    public function getProof(string $procedureId, string $signerId): string
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        if (!$signerId) {
            throw new ClientException('signerId is required');
        }

        $response = $this->httpClient->request('GET', "members/{$signerId}/proof?format=pdf");
        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error Processing getProof: '.$response->getContent(false), $response->getStatusCode());
        }

        return $response->getContent(false);
    }

    public function cancelProcedure(string $procedureId, ?string $reason = null, ?string $customNote = null): void
    {
        $response = $this->request('DELETE', 'procedures/'.$procedureId);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Cancel signature error', 500);
        }
    }

    public function archiveDocument(string $fileName, string $content): void
    {
        $response = $this->request('POST', 'archives', ['json' => [
            'fileName' => $fileName,
            'content' => $content,
        ]]);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Archive Document error', 500);
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed, mixed>|string
     */
    private function request(string $method, string $url, array $options = [])
    {
        $response = $this->httpClient->request($method, $url, $options);
        if (300 <= $response->getStatusCode()) {
            throw new ApiException($response->getContent(false), $response->getStatusCode(), null, json_decode($response->getContent(false), true));
        }

        if (($data = json_decode($response->getContent(false), true)) === null) {
            throw new ClientException('Error get result', $response->getStatusCode(), null, []);
        }

        return $data;
    }

    public function setAppUri(string $appUri): void
    {
        $this->appUri = $appUri;
    }

    public function sendField(string $procedureId, string $signerId, string $documentId, Location $location): string
    {
        throw new ClientException("'sendField' method is no longer implemented for this Yousing v2.", 501);
    }
}
