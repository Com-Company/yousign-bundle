<?php

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\Constants\Versions;
use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Fields;
use ComCompany\YousignBundle\DTO\Member;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\Response\SignatureResponse;

class YousignClient implements ClientInterface
{
    /** @var ClientInterface[] */
    private array $implementations;

    /** @param iterable<ClientInterface> $clientImplementations */
    public function __construct(iterable $clientImplementations)
    {
        $this->implementations = $clientImplementations instanceof \Traversable ? iterator_to_array($clientImplementations) : (array) $clientImplementations;
    }

    private function getInstance(string $version): ClientInterface
    {
        return $this->implementations[$version];
    }

    public function start(Fields $fields, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null, string $version = Versions::V3): SignatureResponse
    {
        return $this->getInstance($version)->start($fields, $config, $memberConfig);
    }

    public function initiateProcedure(?ProcedureConfig $config, string $version = Versions::V3): string
    {
        return $this->getInstance($version)->initiateProcedure($config);
    }

    public function sendSigner(string $procedureId, Member $member, string $version = Versions::V3): string
    {
        return $this->getInstance($version)->sendSigner($procedureId, $member);
    }

    public function sendDocument(string $procedureId, Document $document, string $version = Versions::V3): string
    {
        return $this->getInstance($version)->sendDocument($procedureId, $document);
    }

    public function getProof(string $procedureId, string $signerId, string $version = Versions::V3): string
    {
        return $this->getInstance($version)->getProof($procedureId, $signerId);
    }

    public function getProcedure(string $procedureId, string $version = Versions::V3): SignatureResponse
    {
        return $this->getInstance($version)->getProcedure($procedureId);
    }

    public function deleteProcedure(string $procedureId, string $version = Versions::V3): void
    {
        $this->getInstance($version)->deleteProcedure($procedureId);
    }

    public function downloadDocument(string $procedureId, string $documentId, string $version = Versions::V3): string
    {
        return $this->getInstance($version)->downloadDocument($procedureId, $documentId);
    }

    public function activate(string $procedureId, string $version = Versions::V3): SignatureResponse
    {
        return $this->getInstance($version)->activate($procedureId);
    }
}
