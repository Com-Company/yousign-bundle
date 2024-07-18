<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\YousignBundle\DTO\Field\Field;

class FieldLocation
{
    private Member $member;
    private Document $document;
    private Field $location;

    public function __construct(Member $member, Document $document, Field $location)
    {
        $this->member = $member;
        $this->document = $document;
        $this->location = $location;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getLocation(): Field
    {
        return $this->location;
    }

    public function setLocation(Field $location): self
    {
        $this->location = $location;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(get_object_vars($this), [
            'member' => $this->member->toArray(),
            'document' => $this->document->toArray(),
            'location' => $this->location->toArray(),
        ]);
    }
}
