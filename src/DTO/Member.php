<?php

namespace ComCompany\YousignBundle\DTO;
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
            'infos' => [
                "first_name" => $this->firstName,
                "last_name" => $this->lastName,
                "email" =>  $this->email,
                "phone_number" => $this->phone,
                "locale" => "fr",
            ],
            'fields' => array_map(static fn ($field) => $field->toArray(), $this->fields),
            'signature_level' => $this->getConfig()->signatureType,
        ];
    }

    public function toArray(): array
    {
        return array_merge(
            get_object_vars($this),
            [
                'fields' => array_map(static fn ($field) => $field->toArray(), $this->fields),
                'config' => $this->getConfig()->toArray()
            ],
        );
    }
}