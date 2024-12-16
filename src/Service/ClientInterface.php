<?php

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Field\Field;
use ComCompany\YousignBundle\DTO\FieldsLocations;
use ComCompany\YousignBundle\DTO\Follower;
use ComCompany\YousignBundle\DTO\Member;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
use ComCompany\YousignBundle\DTO\Response\Audit\AuditResponse;
use ComCompany\YousignBundle\DTO\Response\DocumentResponse;
use ComCompany\YousignBundle\DTO\Response\FollowerResponse;
use ComCompany\YousignBundle\DTO\Response\ProcedureResponse;
use ComCompany\YousignBundle\DTO\Response\Signature\SignatureResponse;
use ComCompany\YousignBundle\DTO\Response\SignerResponse;
use ComCompany\YousignBundle\Exception\ApiException;
use ComCompany\YousignBundle\Exception\ClientException;

interface ClientInterface
{
    /**
     * Allow to create entire signature process, with provided parameters.
     *
     * @param ProcedureConfig|null $config       params to initiate new signature request
     * @param MemberConfig|null    $memberConfig params to initiate members configs like signature signature_level and signature authentication
     *
     * @return SignatureResponse DTO with all information about the signature
     *
     * @throws ApiException|ClientException
     */
    public function start(FieldsLocations $signatureLocationList, ?ProcedureConfig $config = null, ?MemberConfig $memberConfig = null): SignatureResponse;

    /**
     * Initiate a new signature request.
     */
    public function initiateProcedure(?ProcedureConfig $config): ProcedureResponse;

    /** Create a new Signer. */
    public function sendSigner(string $procedureId, Member $member): SignerResponse;

    /**
     * Create a follower.
     *
     * @param Follower[] $followers
     *
     * @return FollowerResponse[]
     */
    public function sendFollowers(string $procedureId, iterable $followers): iterable;

    /**
     * Add fields.
     */
    public function sendField(string $procedureId, string $signerId, string $documentId, Field $location): string;

    /**
     * Add Document to a Signature Request.
     */
    public function sendDocument(string $procedureId, Document $document): DocumentResponse;

    /**
     * Update document nature.
     */
    public function updateDocumentNature(string $procedureId, string $documentId, string $nature): DocumentResponse;

    /**
     * Activate procedure.
     */
    public function activate(string $procedureId): SignatureResponse;

    /**
     * Get Signer Proof.
     */
    public function getProof(string $procedureId, string $signerId): string;

    /**
     * Fetch a signature request.
     */
    public function getProcedure(string $procedureId): SignatureResponse;

    /**
     * Cancel a Signature Request.
     */
    public function cancelProcedure(string $procedureId, ?string $reason = null, ?string $customNote = null): void;

    /**
     * Delete a Signature Request.
     */
    public function deleteProcedure(string $procedureId): void;

    /** Get a Document. */
    public function downloadDocument(string $procedureId, string $documentId): string;

    /** Get an Audit Trail (useful to get Signature date in yousign V3). */
    public function getAuditTrail(string $procedureId, string $signerId): AuditResponse;

    /**
     * Check RIB document.
     */
    public function checkRib(string $path): string;
}
