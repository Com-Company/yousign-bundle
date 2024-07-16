<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Fields;
use ComCompany\YousignBundle\DTO\Location;
use ComCompany\YousignBundle\DTO\Member;
use ComCompany\YousignBundle\DTO\Member as MemberDTO;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig as ProcedureConfigYousign;
use ComCompany\YousignBundle\DTO\Response\Audit\AuditResponse;
use ComCompany\YousignBundle\DTO\Response\DocumentResponse;
use ComCompany\YousignBundle\DTO\Response\MemberResponse;
use ComCompany\YousignBundle\DTO\Response\SignatureResponse;
use ComCompany\YousignBundle\Exception\ApiException;
use ComCompany\YousignBundle\Exception\ClientException;
use ComCompany\YousignBundle\Exception\YousignException;
use ComCompany\YousignBundle\Service\ClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientYousign implements ClientInterface
{
    public const DEFAULT_CONFIG = [
        'name' => 'Procédure de signature',
    ];

    public const CANCEL_REASONS = ['contractualization_aborted', 'errors_in_document', 'other'];

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Allow to create entire signature process, with provided parameters.
     *
     * @param Fields               $fields       array of elements which define a field to sign (Member, Document and Location)
     * @param ProcedureConfig|null $config       params to initiate new signature request
     * @param MemberConfig|null    $memberConfig params to initiate members configs like signature signature_level and signature authentication
     *
     * @return SignatureResponse DTO with all information about the signature
     *
     * @throws ApiException|ClientException
     */
    public function start(Fields $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse
    {
        $signature = new SignatureResponse();
        $procedureId = $this->initiateProcedure($config);
        $signature->setProcedureId($procedureId);
        $signers = [];
        $documents = [];

        foreach ($fields->all() as $field) {
            $document = $field->getDocument();

            if (!isset($documents[$document->getId()])) {
                $supplierId = $this->sendDocument($procedureId, $document);
                $documenResponse = new DocumentResponse(
                    $document->getId(),
                    $supplierId,
                    $document->getNature()
                );
                $documents[$document->getId()] = $supplierId;
                $signature->addDocument($documenResponse);
            }

            $member = $field->getMember();
            $memberInfos = $member->toArray();
            $hash = md5(print_r($member->toArray(), true));
            if (!isset($signers[$hash])) {
                $signers[$hash] =
                    new MemberDTO(
                        $memberInfos['firstName'],
                        $memberInfos['lastName'],
                        $memberInfos['email'],
                        $memberInfos['phone'],
                        [],
                        [],
                        $memberConfig,
                        $memberInfos['id']
                    );
            }
            $signers[$hash]->addField(array_merge($field->getLocation()->toArray(), ['document_id' => $documents[$document->getId()]]));
        }

        $members = [];
        foreach ($signers as $signer) {
            $idSigner = $this->sendSigner($procedureId, $signer);
            $members[$idSigner['id']] = $signer;
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

    /**
     * @return mixed[]
     */
    public function sendSigner(string $procedureId, Member $member): array
    {
        if (!$member instanceof MemberDTO) {
            throw new ClientException('Error when adding signer');
        }

        try {
            $uri = 'signature_requests/'.$procedureId.'/signers';
            $response = $this->request('POST', $uri, [
                'body' => json_encode($member->formattedForApi(), JSON_THROW_ON_ERROR),
            ]);

            if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
                throw new ApiException('Create signer error');
            }
        } catch (YousignException $e) {
            $error = $e->getErrors();
            $error['member'] = [
                'first_name' => $member->getFirstName(),
                'last_name' => $member->getFirstName(),
                'email' => $member->getEmail(),
                'phone' => $member->getPhone(),
            ];
            throw new ClientException('Error when adding signer', 400, $e, $error);
        }

        return $response;
    }

    /**
     * @return mixed[]
     */
    public function sendFollower(string $procedureId, string $email, string $locale = 'fr'): array
    {
        $uri = 'signature_requests/'.$procedureId.'/followers';
        $response = $this->request('POST', $uri, [
            'body' => json_encode([
                'email' => $email,
                'locale' => $locale,
            ], JSON_THROW_ON_ERROR),
        ]);

        return is_array($response) ? $response : [$response];
    }

    public function sendField(string $procedureId, string $signerId, string $documentId, Location $location): string
    {
        if (!$location instanceof Location) {
            throw new ClientException('Error when adding field');
        }

        $uri = 'signature_requests/'.$procedureId.'/documents/'.$documentId.'/fields';
        $response = $this->request('POST', $uri, [
            'body' => json_encode(array_merge(
                ['signer_id' => $signerId],
                $location->toArray(),
            ), JSON_THROW_ON_ERROR),
        ]);

        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Create field error');
        }

        return $response['id'];
    }

    public function sendDocument(string $procedureId, Document $document): string
    {
        $file = new \SplFileInfo($document->getPath());
        $formData = new FormDataPart([
            'file' => DataPart::fromPath($file->getPathname(), $document->getName(), $document->getMimeType()),
            'nature' => $document->getNature(),
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

        $response = $this->request('GET', 'signature_requests/'.$procedureId);

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

        $signers = $this->request('GET', "signature_requests/{$procedureId}/signers");
        if (is_array($signers) && !empty($signers)) {
            foreach ($signers as $signer) {
                $signatureResponse->addMember(new MemberResponse(null, $signer['id'], $signer['status'], $signer['signature_link']));
            }
        }

        return $signatureResponse;
    }

    public function activate(string $procedureId): SignatureResponse
    {
        $response = $this->request('POST', "signature_requests/{$procedureId}/activate");
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

        $response = $this->httpClient->request('GET', "signature_requests/{$procedureId}/documents/{$documentId}/download");

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

        $response = $this->httpClient->request('GET', 'signature_requests/{$procedureId}/signers/{$signerId}/audit_trails/download');

        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error Processing Request: '.$response->getContent(false), $response->getStatusCode());
        }

        return $response->getContent(false);
    }

    public function cancelProcedure(string $procedureId, ?string $reason = null, ?string $customNote = null): void
    {
        if (!in_array($reason, self::CANCEL_REASONS, true)) {
            throw new ClientException('Cancel reason must be one of: '.implode(', ', self::CANCEL_REASONS), 400);
        }

        $response = $this->request(
            'POST',
            "signature_requests/{$procedureId}/cancel",
            [
                'body' => json_encode(['reason' => $reason, 'custom_note' => $customNote], JSON_THROW_ON_ERROR),
            ]);
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
        $response = $this->httpClient->request($method, $url, $options);
        if (300 <= $response->getStatusCode()) {
            $errors = $this->handleError($response->getContent(false));
            throw new ApiException($errors['message'] ?? 'ApiException', $response->getStatusCode(), null, $errors ?? []);
        }

        if (($data = json_decode($response->getContent(false), true)) === null) {
            $errors = $this->handleError($response->getContent(false));
            throw new ClientException($errors['message'] ?? 'ClientException', $response->getStatusCode(), null, $errors ?? []);
        }

        return $data;
    }

    /**
     * @return mixed[]
     */
    private function handleError(string $response): array
    {
        $errorsDatas = json_decode($response, true);

        $errors = [];
        $errors['message'] = $errorsDatas['detail'] ?? '';

        if (is_array($errorsDatas['invalid_params'] ?? false)) {
            $errors['errors'] = array_map(static function ($item) {
                return ($item['name'] && $item['reason']) ? [
                    'name' => (preg_match('/\[(.*?)\]/', $item['name'], $matches)) ? $matches[1] : $item['name'],
                    'reason' => $item['reason'],
                ] : $item;
            }, $errorsDatas['invalid_params']);
        }

        return $errors;
    }

    public function getAuditTrail(string $procedureId, string $signerId): AuditResponse
    {
        $response = $this->request(
            'GET',
            "signature_requests/{$procedureId}/signers/{$signerId}/audit_trails",
        );

        if (
            !is_array($response)
            || !is_array($signer = $response['signer'] ?? false)
            || !is_array($signatureRequest = $response['signature_request'] ?? false)
        ) {
            throw new ApiException('getAuditTrail error', 500);
        }

        $audit = new AuditResponse();
        $audit->getSigner()->setId($signer['id']);
        $audit->getSigner()->setFirstname($signer['first_name']);
        $audit->getSigner()->setLastname($signer['last_name']);
        $audit->getSigner()->setPhone($signer['phone_number']);
        $audit->getSigner()->setEmail($signer['email_address']);
        $audit->getSigner()->setConsentGivenAt($signer['consent_given_at']);
        $audit->getSigner()->setSignatureProcessCompleteAt($signer['signature_process_completed_at']);

        $audit->getSignatureRequest()->setId($signatureRequest['id']);
        $audit->getSignatureRequest()->setName($signatureRequest['name']);
        $audit->getSignatureRequest()->setSentAt($signatureRequest['sent_at']);
        $audit->getSignatureRequest()->setExpiredAt($signatureRequest['expired_at']);

        return $audit;
    }
}
