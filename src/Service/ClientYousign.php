<?php

namespace ComCompany\YousignBundle\Service;
use ComCompany\SignatureContract\DTO\Document;
use ComCompany\SignatureContract\DTO\Member;
use ComCompany\SignatureContract\DTO\MemberConfig;
use ComCompany\SignatureContract\DTO\Fields;
use ComCompany\SignatureContract\DTO\FieldLocation;
use ComCompany\SignatureContract\Exception\ApiException;
use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\SignatureContract\Service\SignatureContractInterface;
use ComCompany\SignatureContract\Response\SignatureResponse;
use ComCompany\SignatureContract\DTO\ProcedureConfig;
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
    const DEFAULT_CONFIG = [
        'name' => 'Procédure de signature',
    ];

    const DELIVVERY_TYPES = ['mail', 'none'];

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient) {
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
            if (!in_array($document, $signature->getDocuments(), true)) {
                $document->setSupplierId($this->sendDocument($procedureId, $document));
                $signature->addDocument($document);
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
                        $memberConfig);
            }
            $signers[$hash]->addField(array_merge($field->getLocation()->toArray(), ['document_id' => $document->getSupplierId()]));
        }

        foreach ($signers as $signer) {

            $idSigner = $this->sendSigner($procedureId, $signer);
            $signer->setSupplierId($idSigner);
            $signature->addMember($signer);
        }

        $signatureActivated = $this->activate($procedureId);

        foreach ($signatureActivated['signers'] as $signer) {
            foreach ($signature->getMembers() as $member) {
                if ($member->getSupplierId() === $signer['id']) {
                    $member->setUri($signer['signature_link']);
                }
            }
        }
        
        return $signature;
    }

    public function initiateProcedure(ProcedureConfig $config = null): string {
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
    
    public function sendSigner(string $procedureId, Member $member): string {
        if(!$member instanceof MemberDTO) {
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

    public function sendDocument(string $procedureId, Document $document): string {

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

    public function getProcedure(string $procedureId): array {
        if(!$procedureId) {
            throw new ClientException('procedureId is required');
        }

        $uri = 'signature_requests/'.$procedureId;
        $response = $this->request('GET', $uri);

        if (!is_array($response) || empty($response)) {
            throw new ApiException('Get procedure error');
        }

        return $response;
    }

    /**
     * @param string $idPocedure
     * @return array<string, mixed>
     */
    public function activate(string $idPocedure): array
    {
        $response = $this->request('POST', sprintf('signature_requests/%s/activate', $idPocedure), []);
        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Activate signature error', 500);
        }

        return $response;
    }

    /**
     * @param string $procedureId
     * @param string $documentId
     * @return array<mixed, mixed>|string
     */
    public function downloadDocument(string $procedureId, string $documentId) {
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

    public function getSignerAuditTrail(string $procedureId, string $signerId) {
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

    /**
     * @param string $method
     * @param string $url
     * @param array<string, mixed> $options
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
