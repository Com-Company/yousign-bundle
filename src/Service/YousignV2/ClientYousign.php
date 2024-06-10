<?php

namespace ComCompany\YousignBundle\Service\YousignV2;

use ComCompany\SignatureContract\DTO\Document;
use ComCompany\SignatureContract\DTO\Fields;
use ComCompany\SignatureContract\DTO\Member;
use ComCompany\SignatureContract\DTO\MemberConfig;
use ComCompany\SignatureContract\DTO\ProcedureConfig;
use ComCompany\SignatureContract\Exception\ApiException;
use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\SignatureContract\Response\SignatureResponse;
use ComCompany\SignatureContract\Service\SignatureContractInterface;
use Safe\Exceptions\StringsException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;
use function Safe\sprintf;

class ClientYousign implements SignatureContractInterface
{
    private HttpClientInterface $httpClient;

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

    /** @throws ClientException */
    public function sendSigner(string $procedureId, Member $member): string
    {
        throw new ClientException("'sendSigner' method is no longer implemented for this Yousing v2.", 501);
    }

    /** @throws ClientException */
    public function sendDocument(string $procedureId, Document $document): string
    {
        throw new ClientException("'sendDocument' method is no longer implemented for this Yousing v2.", 501);
    }

    /**
     * @throws ClientException
     * @throws ApiException|StringsException
     */
    public function getProcedure(string $procedureId): array
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $response = $this->request('GET', sprintf('procedures/%s', $procedureId));

        if (!is_array($response) || empty($response)) {
            throw new ApiException('Get procedure error');
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function activate(string $idPocedure): array
    {
        $response = $this->request('PUT', sprintf('procedures/%s', $idPocedure), ['json' => ['start' => true]]);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Activate signature error', 500);
        }

        return $response;
    }

    /**
     * @return array<mixed, mixed>|string
     */
    public function downloadDocument(string $procedureId, string $documentId)
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        if (!$documentId) {
            throw new ClientException('documentId is required');
        }

        $response = $this->httpClient->request('GET', sprintf('%s/download', $documentId), []);

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

        $response = $this->httpClient->request('GET', sprintf('%s/proof?format=pdf', $signerId), []);

        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error Processing getProof: '.$response->getContent(false), $response->getStatusCode());
        }

        return $response->getContent(false);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteProcedure(string $procedureId): array
    {
        $response = $this->request('DELETE', sprintf('procedures/%s', $procedureId), []);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Cancel signature error', 500);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed, mixed>|string
     */
    private function request(string $method, string $url, array $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            if (300 <= $response->getStatusCode()) {
                throw new ApiException('Error Processing Request: '.$response->getContent(false), $response->getStatusCode());
            }

            if (($data = json_decode($response->getContent(false), true)) === null) {
                throw new ClientException('Error get result', $response->getStatusCode());
            }

            return $data;
        } catch (TransportExceptionInterface $e) {
            throw new ApiException('Error Processing Request : '.$e->getMessage(), $e->getCode(), $e);
        }
    }
}
