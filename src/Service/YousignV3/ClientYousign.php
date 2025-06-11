<?php

namespace ComCompany\YousignBundle\Service\YousignV3;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Field\Field;
use ComCompany\YousignBundle\DTO\FieldsLocations;
use ComCompany\YousignBundle\DTO\Follower;
use ComCompany\YousignBundle\DTO\Member as MemberDTO;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig as ProcedureConfigYousign;
use ComCompany\YousignBundle\DTO\Response\Audit\AuditResponse;
use ComCompany\YousignBundle\DTO\Response\DocumentResponse;
use ComCompany\YousignBundle\DTO\Response\FollowerResponse;
use ComCompany\YousignBundle\DTO\Response\ProcedureResponse;
use ComCompany\YousignBundle\DTO\Response\RateLimit as RateLimitDTO;
use ComCompany\YousignBundle\DTO\Response\Signature\DeclineInformation;
use ComCompany\YousignBundle\DTO\Response\Signature\Document as SignatureDocumentResponse;
use ComCompany\YousignBundle\DTO\Response\Signature\Member;
use ComCompany\YousignBundle\DTO\Response\Signature\SignatureResponse;
use ComCompany\YousignBundle\DTO\Response\SignerResponse;
use ComCompany\YousignBundle\Exception\ApiException;
use ComCompany\YousignBundle\Exception\ApiRateLimitException;
use ComCompany\YousignBundle\Exception\ClientException;
use ComCompany\YousignBundle\Exception\YousignException;
use ComCompany\YousignBundle\Service\ClientInterface;
use ComCompany\YousignBundle\Service\Utils\DateUtils;
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
     * @param FieldsLocations      $fields       array of elements which define a field to sign (Member, Document and Location)
     * @param ProcedureConfig|null $config       params to initiate new signature request
     * @param MemberConfig|null    $memberConfig params to initiate members configs like signature signature_level and signature authentication
     *
     * @return SignatureResponse DTO with all information about the signature
     *
     * @throws ApiException|ClientException
     */
    public function start(FieldsLocations $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse
    {
        $signature = new SignatureResponse();
        $procedure = $this->initiateProcedure($config);
        $signature->setProcedureId($procedure->getId());
        $signers = [];
        $documents = [];

        foreach ($fields->all() as $field) {
            $document = $field->getDocument();

            if (!isset($documents[$document->getId()])) {
                $supplierDocData = $this->sendDocument($procedure->getId(), $document);
                $documentResponse = new SignatureDocumentResponse(
                    $document->getId(),
                    $supplierDocData->getId(),
                    $document->getNature()
                );
                $documents[$document->getId()] = $supplierDocData->getId();
                $signature->addDocument($documentResponse);
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
            $supplierSignerData = $this->sendSigner($procedure->getId(), $signer);
            $members[$supplierSignerData->getId()] = $signer;
        }

        $signatureActivated = $this->activate($procedure->getId());
        foreach ($members as $idSigner => $originalSigner) {
            foreach ($signatureActivated->getMembers() as $signer) {
                if ($idSigner === $signer->getSupplierId()) {
                    $memberResponse = new Member(
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

    public function initiateProcedure(?ProcedureConfig $config = null): ProcedureResponse
    {
        $configData = $config instanceof ProcedureConfigYousign
            ? $config->toArray()
            : self::DEFAULT_CONFIG;

        $response = $this->request('POST', 'signature_requests', [
            'body' => json_encode($configData, JSON_THROW_ON_ERROR),
        ]);

        $datas = $response['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ApiException('create signature_requests error', 500);
        }

        $rateLimit = $datas['rateLimit'] ?? null;
        $rateLimit ? new RateLimitDTO($rateLimit['limitHour'], $rateLimit['remainingHour'], $rateLimit['limitMinute'], $rateLimit['remainingMinute']) : null;

        return new ProcedureResponse($datas['id'], $datas['status'], DateUtils::toDatetime($datas['expiration_date']), $rateLimit);
    }

    public function sendSigner(string $procedureId, MemberDTO $member): SignerResponse
    {
        try {
            $uri = 'signature_requests/'.$procedureId.'/signers';
            $response = $this->request('POST', $uri, [
                'body' => json_encode($member->formattedForApi(), JSON_THROW_ON_ERROR),
            ]);
            $datas = $response['datas'] ?? [];
            if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
                throw new ApiException('Create signer error');
            }

            return new SignerResponse($datas['id'], $datas['status']);
        } catch (YousignException $e) {
            $error = $e->getErrors();
            $error['member'] = [
                'first_name' => $member->getFirstName(),
                'last_name' => $member->getLastName(),
                'email' => $member->getEmail(),
                'phone' => $member->getPhone(),
            ];
            throw new ClientException('Error on sendSigner: '.json_encode($error), 400, $e, $error);
        }
    }

    /**
     * @param Follower[] $followers
     *
     * @return FollowerResponse[]
     */
    public function sendFollowers(string $procedureId, iterable $followers, string $locale = 'fr'): iterable
    {
        $followersArray = is_array($followers) ? $followers : iterator_to_array($followers);

        $uri = 'signature_requests/'.$procedureId.'/followers';
        $response = $this->request('POST', $uri, [
            'body' => json_encode(array_map(static fn ($follower) => $follower->toArray(), $followersArray), JSON_THROW_ON_ERROR),
        ]);
        $datas = $response['datas'] ?? [];
        if (!is_array($datas)) {
            throw new ApiException('Create follower error');
        }

        return array_map(static fn ($follower) => new FollowerResponse($follower['email'], $follower['locale'], $follower['follower_link']), $datas);
    }

    public function sendField(string $procedureId, string $signerId, string $documentId, Field $location): string
    {
        $uri = 'signature_requests/'.$procedureId.'/documents/'.$documentId.'/fields';
        $response = $this->request('POST', $uri, [
            'body' => json_encode(array_merge(
                ['signer_id' => $signerId],
                $location->toArray(),
            ), JSON_THROW_ON_ERROR),
        ]);
        $datas = $response['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ApiException('Create field error');
        }

        return $datas['id'];
    }

    public function sendDocument(string $procedureId, Document $document): DocumentResponse
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
        $datas = $responseYousign['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ClientException('Upload error', 500);
        }

        return new DocumentResponse($datas['id'], $datas['total_pages'], DateUtils::toDatetime($datas['created_at']));
    }

    public function updateDocumentNature(string $procedureId, string $documentId, string $nature): DocumentResponse
    {
        $formData = new FormDataPart([
            'nature' => $nature,
        ]);
        $header = $formData->getPreparedHeaders();
        $responseYousign = $this->request('POST', 'signature_requests/'.$procedureId.'/documents/'.$documentId, [
            'headers' => $header->toArray(),
            'body' => $formData->toIterable(),
        ]);

        $datas = $responseYousign['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ClientException('Upload error', 500);
        }

        return new DocumentResponse($datas['id'], $datas['total_pages'], DateUtils::toDatetime($datas['created_at']));
    }

    public function getProcedure(string $procedureId): SignatureResponse
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $response = $this->request('GET', 'signature_requests/'.$procedureId);
        $datas = $response['datas'] ?? [];
        if (!is_array($datas) || empty($datas)) {
            throw new ApiException('Get procedure error');
        }
        $rateLimit = $datas['rateLimit'] ?? null;
        $signatureResponse = new SignatureResponse();
        $signatureResponse->setProcedureId($datas['id'])
            ->setCreationDate(DateUtils::toDatetime($datas['created_at'] ?? ''))
            ->setExpirationDate($datas['expiration_date'] ? DateUtils::toDatetime($datas['expiration_date']) : null)
            ->setWorkspaceId($datas['workspace_id'])
            ->setStatus($datas['status'])
            ->setRateLimit($rateLimit ? new RateLimitDTO($rateLimit['limitHour'], $rateLimit['remainingHour'], $rateLimit['limitMinute'], $rateLimit['remainingMinute']) : null)
        ;

        foreach ($datas['documents'] as $document) {
            $signatureResponse->addDocument(new SignatureDocumentResponse(null, $document['id'], $document['nature']));
        }

        $resp = $this->request('GET', "signature_requests/{$procedureId}/signers");
        $signers = $resp['datas'] ?? [];
        if (is_array($signers) && !empty($signers)) {
            foreach ($signers as $signer) {
                $signatureResponse->addMember(new Member(
                    null,
                    $signer['id'],
                    $signer['status'],
                    $signer['signature_link'],
                    null,
                    $signer['info']['first_name'] ?? null,
                    $signer['info']['last_name'] ?? null,
                    $signer['info']['email'] ?? null,
                    $signer['info']['phone_number'] ?? null,
                ));
            }
        }

        if ($decline = $datas['decline_information'] ?? false) {
            $declinedAt = $decline['declined_at'] ? DateUtils::toDatetime($decline['declined_at']) : null;
            $signatureResponse->setDeclineInformation(new DeclineInformation($decline['reason'], $decline['signer_id'], $declinedAt));
        }

        return $signatureResponse;
    }

    public function activate(string $procedureId): SignatureResponse
    {
        $response = $this->request('POST', "signature_requests/{$procedureId}/activate");
        $datas = $response['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ApiException('Activate signature error', 500);
        }

        $signatureResponse = new SignatureResponse();
        $signatureResponse->setProcedureId($datas['id'])
            ->setCreationDate(DateUtils::toDatetime($datas['created_at'] ?? ''))
            ->setExpirationDate($datas['expiration_date'] ? DateUtils::toDatetime($datas['expiration_date']) : null)
            ->setWorkspaceId($datas['workspace_id'])
            ->setStatus($datas['status']);

        foreach ($datas['documents'] as $document) {
            $signatureResponse->addDocument(new SignatureDocumentResponse(null, $document['id'], $document['nature']));
        }

        if (is_array($datas['signers']) && !empty($datas['signers'])) {
            foreach ($datas['signers'] as $signer) {
                $signatureResponse->addMember(new Member(null, $signer['id'], $signer['status'], $signer['signature_link']));
            }
        }

        if ($decline = $datas['decline_information'] ?? false) {
            $declinedAt = $decline['declined_at'] ? DateUtils::toDatetime($decline['declined_at']) : null;
            $signatureResponse->setDeclineInformation(new DeclineInformation($decline['reason'], $decline['signer_id'], $declinedAt));
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

        $response = $this->httpClient->request('GET', "signature_requests/{$procedureId}/signers/{$signerId}/audit_trails/download");

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
        $datas = $response['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ApiException('Cancel signature error', 500);
        }
    }

    public function deleteProcedure(string $procedureId): void
    {
        if (!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $response = $this->httpClient->request('DELETE', "signature_requests/{$procedureId}");
        if (300 <= $response->getStatusCode()) {
            throw new ApiException('Error deleting procedure Request: '.$response->getContent(false), $response->getStatusCode());
        }
    }

    public function archiveDocument(string $workspaceId, Document $document): DocumentResponse
    {
        $file = new \SplFileInfo($document->getPath());
        $formData = new FormDataPart([
            'file' => DataPart::fromPath($file->getPathname(), $document->getName(), $document->getMimeType()),
            'workspace_id' => $workspaceId,
        ]);
        $header = $formData->getPreparedHeaders();
        $responseYousign = $this->request('POST', 'archives', [
            'headers' => $header->toArray(),
            'body' => $formData->toIterable(),
        ]);
        $datas = $responseYousign['datas'] ?? [];
        if (!is_array($datas) || empty($datas['id']) || !is_string($datas['id'])) {
            throw new ClientException('Upload archiveDocument error', 500);
        }

        return new DocumentResponse($datas['id'], 0, DateUtils::toDatetime($datas['created_at']));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed, mixed>
     */
    private function request(string $method, string $url, array $options = [])
    {
        $response = $this->httpClient->request($method, $url, $options);
        $code = $response->getStatusCode();
        $rateLimit = $this->getRateLimit($response->getHeaders(false));
        $content = $response->getContent(false);
        if (300 <= $code) {
            $errors = $this->handleError($content);
            if (429 === $code) {
                throw new ApiRateLimitException('Limite d\'appels atteinte, merci de réessayer ultérieurement; '.$rateLimit->getRateLimitDetail(), $response->getStatusCode(), null, $errors);
            }

            throw new ApiException($content, $response->getStatusCode(), null, $errors);
        }

        if (($data = json_decode($content, true)) === null) {
            $errors = $this->handleError($response->getContent(false));
            throw new ClientException($content, $response->getStatusCode(), null, $errors);
        }

        $datas['rateLimit'] = $rateLimit->toArray();
        $datas['datas'] = $data;

        return $datas;
    }

    /**
     * @return mixed[]
     */
    private function handleError(string $response): array
    {
        $errorsDatas = json_decode($response, true);

        $errors = [];
        $errors['message'] = $errorsDatas['detail'] ?? ($errorsDatas['message'] ?? $response);

        if (is_array($errorsDatas['invalid_params'] ?? false)) {
            $errors['errors'] = array_map(static function ($item) {
                return ($item['name'] && $item['reason']) ? [
                    // if name contains '].' like [0].email is mostly an error on specific key in sended array; othervise it's a field name in nested element like info[email]
                    'name' => (false !== strpos($item['name'], '].')) ? $item['name'] : ((preg_match('/\[(.*?)\]/', $item['name'], $matches)) ? $matches[1] : $item['name']),
                    'reason' => $item['reason'],
                ] : $item;
            }, $errorsDatas['invalid_params']);
        }

        return $errors;
    }

    /**  @throws ClientException|ApiException */
    public function getAuditTrail(string $procedureId, string $signerId): AuditResponse
    {
        $response = $this->request(
            'GET',
            "signature_requests/{$procedureId}/signers/{$signerId}/audit_trails",
        );
        $datas = $response['datas'] ?? [];
        if (
            !is_array($response)
            || !is_array($signer = $datas['signer'] ?? false)
            || !is_array($signatureRequest = $datas['signature_request'] ?? false)
        ) {
            throw new ApiException('getAuditTrail error', 500);
        }

        $audit = new AuditResponse();
        $audit->getSigner()
            ->setId($signer['id'])
            ->setFirstname($signer['first_name'])
            ->setLastname($signer['last_name'])
            ->setPhone($signer['phone_number'])
            ->setEmail($signer['email_address'])
            ->setConsentGivenAt(DateUtils::toDatetime($signer['consent_given_at'] ?? ''))
            ->setSignatureProcessCompleteAt(DateUtils::toDatetime($signer['signature_process_completed_at'] ?? ''));

        $audit->getSignatureRequest()
            ->setId($signatureRequest['id'])
            ->setName($signatureRequest['name'])
            ->setSentAt(DateUtils::toDatetime($signatureRequest['sent_at']))
            ->setExpiredAt(DateUtils::toDatetime($signatureRequest['expired_at']));

        return $audit;
    }

    public function checkRib(string $path): string
    {
        throw new ClientException("'checkRib' method is not implemented for this Yousing v3.", 501);
    }

    /** @param array<string, mixed> $headers */
    private function getRateLimit(array $headers): RateLimitDTO
    {
        $getHeaderValue = function (string $header) use ($headers): ?int {
            return isset($headers[$header][0]) ? (int) $headers[$header][0] : null;
        };

        $limitHour = $getHeaderValue('x-ratelimit-limit-hour');
        $remainingHour = $getHeaderValue('x-ratelimit-remaining-hour');
        $limitMinute = $getHeaderValue('x-ratelimit-limit-minute');
        $remainingMinute = $getHeaderValue('x-ratelimit-remaining-minute');

        return new RateLimitDTO($limitHour, $remainingHour, $limitMinute, $remainingMinute);
    }

    public function sendReminder(string $procedureId, string $signerId): void
    {
        $response = $this->httpClient->request('POST', "signature_requests/{$procedureId}/signers/{$signerId}/send_reminder");
        if (300 <= $response->getStatusCode()) {
            $errors = $this->handleError($response->getContent(false));
            throw new ApiException('Error on sendReminder: '.$response->getContent(false), $response->getStatusCode(), null, $errors);
        }
    }
}
