<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class BankAccountOwner
{
    private ?NaturalPerson $naturalPerson;
    private ?LegalPerson $legalPerson;

    private function __construct(?NaturalPerson $naturalPerson, ?LegalPerson $legalPerson)
    {
        $this->naturalPerson = $naturalPerson;
        $this->legalPerson = $legalPerson;
    }

    public static function fromNaturalPerson(NaturalPerson $person): self
    {
        return new self($person, null);
    }

    public static function fromLegalPerson(LegalPerson $person): self
    {
        return new self(null, $person);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        if (null !== $this->naturalPerson) {
            return ['natural_person' => $this->naturalPerson->toArray()];
        }

        if (null !== $this->legalPerson) {
            return ['legal_person' => $this->legalPerson->toArray()];
        }

        throw new \LogicException('BankAccountOwner must contain either a natural or legal person.');
    }
}
