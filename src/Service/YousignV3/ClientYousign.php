<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\SignatureContract\DTO\Document;
use ComCompany\SignatureContract\DTO\Fields;
use ComCompany\SignatureContract\DTO\Member;
use ComCompany\SignatureContract\DTO\MemberConfig;
use ComCompany\SignatureContract\DTO\ProcedureConfig;
use ComCompany\SignatureContract\Exception\ApiException;
use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\SignatureContract\Response\DocumentResponse;
use ComCompany\SignatureContract\Response\MemberResponse;
use ComCompany\SignatureContract\Response\SignatureResponse;
use ComCompany\SignatureContract\Service\SignatureContractInterface;
use ComCompany\YousignBundle\DTO\Member as MemberDTO;
use ComCompany\YousignBundle\DTO\ProcedureConfig as ProcedureConfigYousign;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;
use function Safe\sprintf;

class ClientYousign implements SignatureContractInterface
{
    public const DEFAULT_CONFIG = [
        'name' => 'Procédure de signature',
    ];

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function start(Fields $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse
    {
        $signature = new SignatureResponse();
        $procedureId = $this->initiateProcedure($config);
        $signature->setProcedureId($procedureId);

        $signers = [];
        foreach ($fields->all() as $field) {
            $document = $field->getDocument();
            $supplierId = null;
            if (!in_array($document, $signature->getDocuments(), true)) {
                $supplierId = $this->sendDocument($procedureId, $document);
                $documenResponse = new DocumentResponse(
                    $document->getId(),
                    $supplierId,
                    'signable_document'
                );
                $signature->addDocument($documenResponse);
            }

            $member = $field->getMember();
            $memberInfos = $member->toArray();
            $hash = md5(print_r($member->toArray(), true));
            if (!isset($signers[$hash])) {
                $signers[$hash] =
                    new MemberDTO(
                        $memberInfos['id'],
                        $memberInfos['firstName'],
                        $memberInfos['lastName'],
                        $memberInfos['email'],
                        $memberInfos['phone'],
                        [],
                        [],
                        $memberConfig);
            }
            $signers[$hash]->addField(array_merge($field->getLocation()->toArray(), ['document_id' => $supplierId]));
        }

        $members = [];
        foreach ($signers as $signer) {
            $idSigner = $this->sendSigner($procedureId, $signer);
            $members[$idSigner] = $signer;
        }

        $signatureActivated = $this->activate($procedureId);
        foreach ($members as $idSigner => $originalSigner) {
            foreach ($signatureActivated->getMembers() as $signer) {
                if ($idSigner === $signer->getSupplierId()) {
                    $memberResponse = new MemberResponse(
                        $originalSigner->getId(),
                        $idSigner,
                        'pending',
                        $signer->getUri()
                    );

                    $signature->addMember($memberResponse);
                }
            }
        }

        return $signature;
    }

    public function initiateProcedure(?ProcedureConfig $config = null): string
    {
        $configData = $config instanceof ProcedureConfigYousign
            ? $config->toArray()
            : self::DEFAULT_CONFIG;

        $response = $this->request('POST', 'signature_requests', [
            'body' => json_encode($configData, JSON_THROW_ON_ERROR),
        ]);

        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('create signature_requests error', 500);
        }

        return $response['id'];
    }

    public function sendSigner(string $procedureId, Member $member): string
    {
        if (!$member instanceof MemberDTO) {
            throw new ClientException('Error when adding signer');
        }

        $uri = 'signature_requests/'.$procedureId.'/signers';
        $response = $this->request('POST', $uri, [
            'body' => json_encode($member->formattedForApi(), JSON_THROW_ON_ERROR),
        ]);

        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Create signer error');
        }

        return $response['id'];
    }

    public function sendDocument(string $procedureId, Document $document): string
    {
        $file = new \SplFileInfo($document->getPath());
        $formData = new FormDataPart([
            'file' => DataPart::fromPath($file->getPathname(), $document->getName(), $document->getMimeType()),
            'nature' => 'signable_document',
        ]);
        $header = $formData->getPreparedHeaders();
        $responseYousign = $this->request('POST', 'signature_requests/'.$procedureId.'/documents', [
            'headers' => $header->toArray(),
            'body' => $formData->toIterable(),
        ]);
        if (!is_array($responseYousign) || empty($responseYousign['id']) || !is_string($responseYousign['id'])) {
            throw new ClientException('Upload error', 500);
        }

        return $responseYousign['id'];
    }

    public function getProcedure(string $procedureId): SignatureResponse
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $response = $this->request('GET', sprintf('signature_requests/%s', $procedureId));

        if (!is_array($response) || empty($response)) {
            throw new ApiException('Get procedure error');
        }

        $signatureResponse = new SignatureResponse();
        $signatureResponse->setProcedureId($response['id']);
        $signatureResponse->setCreationDate($response['created_at']);
        $signatureResponse->setExpirationDate($response['expiration_date']);
        $signatureResponse->setWorkspaceId($response['workspace_id']);

        foreach ($response['documents'] as $document) {
            $signatureResponse->addDocument(new DocumentResponse(null, $document['id'], $document['nature']));
        }

        $signers = $this->request('GET', sprintf('signature_requests/%s/signers', $procedureId));
        if (is_array($signers) && !empty($signers)) {
            foreach ($signers as $signer) {
                $signatureResponse->addMember(new MemberResponse(null, $signer['id'], $signer['status'], $signer['signature_link']));
            }
        }

        return $signatureResponse;
    }

    public function activate(string $procedureId): SignatureResponse
    {
        $response = $this->request('POST', sprintf('signature_requests/%s/activate', $procedureId), []);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Activate signature error', 500);
        }

        $signatureResponse = new SignatureResponse();
        $signatureResponse->setProcedureId($response['id']);
        $signatureResponse->setCreationDate($response['created_at']);
        $signatureResponse->setExpirationDate($response['expiration_date']);
        $signatureResponse->setWorkspaceId($response['workspace_id']);

        foreach ($response['documents'] as $document) {
            $signatureResponse->addDocument(new DocumentResponse(null, $document['id'], $document['nature']));
        }

        if (is_array($response['signers']) && !empty($response['signers'])) {
            foreach ($response['signers'] as $signer) {
                $signatureResponse->addMember(new MemberResponse(null, $signer['id'], $signer['status'], $signer['signature_link']));
            }
        }

        return $signatureResponse;
    }

    public function downloadDocument(string $procedureId, string $documentId): string
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        if (!$documentId) {
            throw new ClientException('documentId is required');
        }

        $response = $this->httpClient->request('GET', sprintf('signature_requests/%s/documents/%s/download', $procedureId, $documentId), []);

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

        $response = $this->httpClient->request('GET', sprintf('signature_requests/%s/signers/%s/audit_trails/download', $procedureId, $signerId), []);

        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error Processing Request: '.$response->getContent(false), $response->getStatusCode());
        }

        return $response->getContent(false);
    }

    public function deleteProcedure(string $procedureId): void
    {
        $response = $this->request('POST', sprintf('signature_requests/%s/cancel', $procedureId), []);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Cancel signature error', 500);
        }
    }

    public function archiveDocument(string $fileName, string $content): void
    {
        // Not yet implemented in V3
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
                throw new ApiException($response->getContent(false), $response->getStatusCode());
            }

            if (($data = json_decode($response->getContent(false), true)) === null) {
                throw new ClientException('Error get result', $response->getStatusCode());
            }

            return $data;
        } catch (TransportExceptionInterface $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
