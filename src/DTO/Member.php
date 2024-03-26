<?php

namespace ComCompany\YousignBundle\DTO;
use ComCompany\SignatureContract\DTO\Member as BaseMember;

class Member extends BaseMember
{
    public array $fields = [];

    public function __construct(string $firstName, string $lastName, string $email, string $phone, array $fields = [])
    {
        parent::__construct($firstName, $lastName, $email, $phone);
        $this->fields = $fields;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['fields' => $this->fields]);
    }
}