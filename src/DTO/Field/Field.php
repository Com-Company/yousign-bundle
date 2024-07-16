<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Field;

class Field
{
    public int $page;
    public string $type;

    public function __construct(int $page, string $type)
    {
        $this->page = $page;
        $this->type = $type;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
