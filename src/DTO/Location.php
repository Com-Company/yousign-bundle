<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class Location
{
    public int $x;
    public int $y;
    public int $page;
    public string $type;

    public function __construct(int $x, int $y, int $page, string $type)
    {
        $this->x = $x;
        $this->y = $y;
        $this->page = $page;
        $this->type = $type;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
