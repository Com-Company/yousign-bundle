<?php

namespace ComCompany\YousignBundle\DTO;

class FieldsLocations
{
    /** @var FieldLocation[] */
    private array $fieldsLocations = [];

    public function addField(FieldLocation $fieldLocation): self
    {
        $this->fieldsLocations[] = $fieldLocation;

        return $this;
    }

    /** @return FieldLocation[] */
    public function all(): array
    {
        return $this->fieldsLocations;
    }

    /** @return Member[] */
    public function getMembers(): array
    {
        $signers = [];
        foreach ($this->fieldsLocations as $fieldLocation) {
            $hash = spl_object_hash($fieldLocation->getMember());

            if (!isset($signers[$hash])) {
                $signers[$hash] = $fieldLocation->getMember();
            }
        }

        return $signers;
    }

    /** @return Document[] */
    public function getDocuments(): array
    {
        $documents = [];
        foreach ($this->fieldsLocations as $fieldLocation) {
            $hash = spl_object_hash($fieldLocation->getDocument());

            if (!isset($documents[$hash])) {
                $documents[$hash] = $fieldLocation->getDocument();
            }
        }

        return $documents;
    }

    /** @return array<int, array<string, mixed>> */
    public function toArray(): array
    {
        $fieldsLocations = [];
        foreach ($this->fieldsLocations as $fieldLocation) {
            $fieldsLocations[] = $fieldLocation->toArray();
        }

        return $fieldsLocations;
    }
}
