<?php

namespace ComCompany\YousignBundle\DTO;

class Initials
{
    public string $alignment;

    public string $y;

    public function __construct(string $alignment, string $y)
    {
        $this->alignment = $alignment;
        $this->y = $y;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
