<?php

namespace ComCompany\YousignBundle\Service;

use ComCompany\YousignBundle\DTO\Document;
use ComCompany\YousignBundle\DTO\Field\Field;
use ComCompany\YousignBundle\DTO\FieldsLocations;
use ComCompany\YousignBundle\DTO\Member;
use ComCompany\YousignBundle\DTO\MemberConfig;
use ComCompany\YousignBundle\DTO\ProcedureConfig;
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

    /** Create a follower. */
    public function sendFollower(string $procedureId, string $email, string $locale = 'fr'): FollowerResponse;

    /**
     * Add fields.
     */
    public function sendField(string $procedureId, string $signerId, string $documentId, Field $location): string;

    /**
     * Add Document to a Signature Request.
     */
    public function sendDocument(string $procedureId, Document $document): DocumentResponse;

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
     * Delete a Signature Request.
     */
    public function cancelProcedure(string $procedureId, ?string $reason = null, ?string $customNote = null): void;

    /** Get a Document. */
    public function downloadDocument(string $procedureId, string $documentId): string;
}
