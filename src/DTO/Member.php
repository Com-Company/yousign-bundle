<?php

namespace ComCompany\YousignBundle\DTO;
use App\DTO\Yousign\SignatureDocumentContactRequest;
use ComCompany\SignatureContract\DTO\Member as BaseMember;
use ComCompany\SignatureContract\DTO\MemberConfig as BaseMemberConfig;

class Member extends BaseMember
{
    /** @var array<int, array<string, mixed>> */
    public array $fields = [];

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * @param array<int, array<string, mixed>> $fields
     * @param BaseMemberConfig|null $config
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        array $fields = [],
        array $additional = [],
        ?BaseMemberConfig $config = null
    ) {
        parent::__construct($firstName, $lastName, $email, $phone, $additional, $config);
        $this->fields = $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function formattedForApi(): array
    {
        return [
            'info' => [
                "first_name" => $this->getFirstName(),
                "last_name" => $this->getLastName(),
                "email" =>  $this->getEmail(),
                "phone_number" => $this->getPhone(),
                "locale" => "fr",
            ],
            'fields' => $this->fields,
            'signature_level' => $this->getConfig()->signatureLevel ?? null,
            'signature_authentication_mode' => $this->getConfig()->signatureAuthentificationMode ?? null,
        ] + $this->getAdditional();
    }

    /**
     * @param array<string, mixed> $field
     */
    public function addField(array $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(get_object_vars($this), [
            'config' => $this->getConfig() ? $this->getConfig()->toArray() : []
        ]);
    }
}