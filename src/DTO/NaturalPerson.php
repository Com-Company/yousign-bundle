<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class NaturalPerson
{
    private string $firstName;
    private string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = str_replace('&', 'et', $firstName);
        $this->lastName  = str_replace('&', 'et', $lastName);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
        ];
    }
}
