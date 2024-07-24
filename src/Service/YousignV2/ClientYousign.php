<?php

namespace ComCompany\YousignBundle\Service\YousignV2;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Field\Field;
use ComCompany\YousignBundle\DTO\FieldsLocations;
use ComCompany\YousignBundle\DTO\Follower;
use ComCompany\YousignBundle\DTO\Member as MemberDTO;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\Response\Audit\AuditResponse;
use ComCompany\YousignBundle\DTO\Response\DocumentResponse;
use ComCompany\YousignBundle\DTO\Response\FollowerResponse;
use ComCompany\YousignBundle\DTO\Response\ProcedureResponse;
use ComCompany\YousignBundle\DTO\Response\Signature\Document as SignatureDocumentResponse;
use ComCompany\YousignBundle\DTO\Response\Signature\Member;
use ComCompany\YousignBundle\DTO\Response\Signature\SignatureResponse;
use ComCompany\YousignBundle\DTO\Response\SignerResponse;
use ComCompany\YousignBundle\Exception\ApiException;
use ComCompany\YousignBundle\Exception\ClientException;
use ComCompany\YousignBundle\Service\ClientInterface;
use ComCompany\YousignBundle\Service\Utils\DateUtils;
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
    public function start(FieldsLocations $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse
    {
        throw new ClientException("'start' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @throws ClientException */
    public function initiateProcedure(?ProcedureConfig $config = null): ProcedureResponse
    {
        throw new ClientException("'initiateProcedure' method is no longer implemented for this Yousing v2.", 501);
    }

    /**  @throws ClientException */
    public function sendSigner(string $procedureId, MemberDTO $member): SignerResponse
    {
        throw new ClientException("'sendSigner' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @param Follower[] $followers
     * @return FollowerResponse[]
     *
     * @throws ClientException
     */
    public function sendFollowers(string $procedureId, $followers): iterable
    {
        throw new ClientException("'sendFollower' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @throws ClientException */
    public function sendDocument(string $procedureId, Document $document): DocumentResponse
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
        $workspace = ($response['workspace'] ?? false) ? $removePrefix($response['workspace']) : null;
        $signatureResponse->setProcedureId($response['id'])
            ->setCreationDate(DateUtils::toDatetime($response['createdAt'] ?? ''))
            ->setStatus($response['status'])
            ->setExpirationDate($response['expiresAt'] ? DateUtils::toDatetime($response['expiresAt']) : null)
            ->setFinishedAt($response['finishedAt'] ? DateUtils::toDatetime($response['finishedAt']) : null)
            ->setWorkspaceId($workspace ?: null);

        foreach (($response['files'] ?? []) as $document) {
            $signatureResponse->addDocument(new SignatureDocumentResponse(null, $removePrefix($document['id']), $document['type']));
        }

        foreach (($response['members'] ?? []) as $member) {
            $signUri = ($this->appUri ?? '')."/procedure/sign?members={$member['id']}";
            $signatureResponse->addMember(new Member(null, $removePrefix($member['id']), $member['status'], $signUri, $member['comment'] ?? null));
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

        $data = base64_decode($response->getContent(false));
        if (!is_string($data)) {
            throw new ApiException('Invalid file content received');
        }

        return $data;
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

    public function sendField(string $procedureId, string $signerId, string $documentId, Field $location): string
    {
        throw new ClientException("'sendField' method is no longer implemented for this Yousing v2.", 501);
    }

    public function getAuditTrail(string $procedureId, string $signerId): AuditResponse
    {
        throw new ClientException("'auditTrail' method is not supported in Yousing v2.", 501);
    }
}
