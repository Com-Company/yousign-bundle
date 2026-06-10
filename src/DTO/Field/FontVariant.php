<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Field;

class FontVariant
{
    public bool $italic;

    public bool $bold;

    public function __construct(
        bool $italic = false,
        bool $bold = false
    ) {
        $this->italic = $italic;
        $this->bold = $bold;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
