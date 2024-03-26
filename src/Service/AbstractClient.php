<?php

namespace ComCompany\YousignBundle\Service;
use ComCompany\SignatureContract\DTO\Member as BaseMember;
use ComCompany\SignatureContract\DTO\Document;
use ComCompany\SignatureContract\DTO\SignatureLocation;
use ComCompany\SignatureContract\Exception\ApiException;
use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\SignatureContract\Service\SignatureContractInterface;
use ComCompany\YousignBundle\DTO\InitiateProcedureParams;
use ComCompany\YousignBundle\DTO\Member;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function \json_decode;


class ClientYousign implements SignatureContractInterface
{
    const DEFAULT_CONFIG = [
        'delivery_mode' => 'none',
        'name' => 'Procédure signature',
    ];

    const DELIVVERY_TYPES = ['mail', 'none'];

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }

    public function start(array $documents, array $members, ?InitiateProcedureParams $config): SignatureReponse
    {
        $signature = new SignatureReponse();
        $idPocedure = $this->initiateProcedure($config);
        $signature->setId($idPocedure);

        $signers = [];
        foreach ($documents as $document) {
            $idDocument = $this->sendDocument($idPocedure, $document);
            $document->setIdSupplier($idDocument);
            $signature->addDocument($document);

            foreach ($document->locations as $location) {
                $hash = spl_object_hash($location->member);

                if(!($signers[$hash] ?? null) instanceof Member) {
                    $signers[$hash] = new Member(...$location->member->toArray());
                }
                $signers[$hash]->fields[] = [
                    ...$this->formatPosition($location),
                    'type' => 'signature',
                    'document_id' => $idDocument,
                ];
            }
        }

        foreach ($signers as $signer) {
            $idSigner = $this->addSigner($idPocedure, $signer);
            $signer->setIdSupplier($idSigner);
            $signature->addMember($signer);
        }

        $this->activate($signature);

        return $signature;

    }

    public function initiateProcedure(?InitiateProcedureParams $config): string {
        $configData = $config instanceof InitiateProcedureParams
            ? $config->toArray()
            : self::DEFAULT_CONFIG;

        if (!in_array(self::DELIVVERY_TYPES, $config->deliveryMode)) {
            throw new \InvalidArgumentException('delivery_mode must be one of: '.implode(', ', self::DELIVVERY_TYPES));
        }
        if (empty($configData->name)) {
            $configData['name'] = self::DEFAULT_CONFIG['name'];
        }

        try {
            $response = $this->request('POST', 'signature_requests', [
                'body' => json_encode($configData, JSON_THROW_ON_ERROR),
            ]);

            if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
                throw new ApplicationException('create signature_requests error');
            }

            return $response['id'];
        } catch (ApplicationException $e) {
            throw new ApplicationException('Error initiating signature request: '.$e->getMessage(), 500, $e);
        }
    }

    public function addSigner(string $procedureId, BaseMember $member): string {
        if(!$member instanceof Member) {
            throw new ClientException('Error when adding signer');
        }

        $uri = 'signature_requests/'.$procedureId.'/signers';
        $response = $this->request('POST', $uri, [
            'body' => json_encode($member->toArray(), JSON_THROW_ON_ERROR),
        ]);

        if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
            throw new ApiException('Create signer error');
        }

        return $response;

    }

    public function sendDocument(string $procedureId, Document $document): string {

          $file = new \SplFileInfo($document->path);
          $formData = new FormDataPart([
              'file' => DataPart::fromPath($file->getPathname(), $document->name, $file->getMimeType()),
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


    public function activate(string $idPocedure): array
    {
            $response = $this->request('POST', sprintf('signature_requests/%s/activate', $signature->getYousignId()), []);
            if (!is_array($response) || empty($response['id']) || !is_string($response['id'])) {
                throw new ApiException('Activate signature error');
            }

            return $response;
    }

    private function request(string $method, string $url, array $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            if (300 <= $response->getStatusCode()) {
                throw new ApiException('Error Processing Request: '.$response->getContent(false), $response->getStatusCode());
            }

            if (($data = json_decode($response->getContent(false), true)) === null) {
                throw new ClientException('Error get result');
            }

            return $data;
        } catch (TransportExceptionInterface $e) {
            throw new ClientException('Error Processing Request : '.$e->getMessage(), $e->getCode());
        }
    }

    private function formatPosition(SignatureLocation $location): array
    {
        return array_filter(
            $location->toArray(),
            static fn($key) => $key !== 'member',
            ARRAY_FILTER_USE_KEY
        );
    }
}
