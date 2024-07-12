<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class FieldLocation
{
    private Member $member;
    private Document $document;
    private Location $location;

    public function __construct(Member $member, Document $document, Location $location)
    {
        $this->member = $member;
        $this->document = $document;
        $this->location = $location;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): void
    {
        $this->member = $member;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
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
