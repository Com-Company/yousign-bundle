<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class LegalPerson
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return [
            'name' => $this->name,
        ];
    }
}
