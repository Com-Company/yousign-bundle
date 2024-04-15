<?php

namespace ComCompany\YousignBundle\DTO;
use App\DTO\Yousign\SignatureDocumentContactRequest;
use ComCompany\SignatureContract\DTO\Member as BaseMember;
use ComCompany\SignatureContract\DTO\MemberConfig;

class Member extends BaseMember
{
    public array $fields = [];
    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        array $fields = [],
        ?MemberConfig $config = null
    ) {
        parent::__construct($firstName, $lastName, $email, $phone, $config);
        $this->fields = $fields;
    }

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
        ];
    }

    public function addField(array $field): void
    {
        $this->fields[] = $field;
    }

    public function toArray(): array
    {
        return array_merge(get_object_vars($this), [
            'config' => $this->getConfig() ? $this->getConfig()->toArray() : []
        ]);
    }
}